<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\HotelBooking;
use App\Models\Notification;
use App\Models\PaymentIntent;
use App\Models\TransportBooking;
use App\Services\Payments\Contracts\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The online-payment half of settlement.
 *
 * Everything money-related still happens the way it always has: a payable is
 * marked paid with a method, and the billing observers raise the invoice and
 * the receipt. This service only owns what is new — holding an attempt while
 * the payer is away on the provider's site, and letting exactly one callback
 * turn that attempt into a settled booking.
 */
class PaymentIntentService
{
    /**
     * What each payable owes and how "paid" is spelled on it. Kept in one place
     * so a new payable type is a single entry rather than another branch in
     * three controllers.
     */
    private const PAYABLES = [
        Booking::class          => ['amount' => 'amount', 'paid_status' => 'paid',  'cancelled' => 'cancelled'],
        HotelBooking::class     => ['amount' => 'amount', 'paid_status' => 'Paid',  'cancelled' => 'Cancelled'],
        TransportBooking::class => ['amount' => 'fare',   'paid_status' => 'Paid',  'cancelled' => 'Cancelled'],
    ];

    /**
     * Open an attempt to pay $payable through $gateway.
     *
     * A payable can only have one live attempt: re-opening the checkout
     * abandons the previous pending intent rather than leaving two references
     * the provider could both call back on.
     *
     * $channel records where the payer started ('app' | 'portal' | 'web') so
     * the result page knows whether to hand them back to the mobile app.
     */
    public function createFor(Model $payable, PaymentGateway $gateway, ?int $customerId = null, string $channel = 'web'): PaymentIntent
    {
        $config = $this->configFor($payable);
        $amount = round((float) $payable->{$config['amount']}, 2);

        if ($amount <= 0) {
            throw new \RuntimeException('There is nothing to pay on this item yet.');
        }

        PaymentIntent::where('payable_type', $payable->getMorphClass())
            ->where('payable_id', $payable->getKey())
            ->where('status', PaymentIntent::STATUS_PENDING)
            ->update(['status' => PaymentIntent::STATUS_CANCELLED, 'failure_reason' => 'Superseded by a new attempt.']);

        return PaymentIntent::create([
            'reference'    => $this->newReference(),
            'gateway'      => $gateway->key(),
            'method'       => $gateway->receiptMethod(),
            'payable_type' => $payable->getMorphClass(),
            'payable_id'   => $payable->getKey(),
            'customer_id'  => $customerId ?? $payable->customer_id,
            'amount'       => $amount,
            'currency'     => 'BDT',
            'status'       => PaymentIntent::STATUS_PENDING,
            'payload'      => ['channel' => $channel],
        ]);
    }

    /**
     * Ask the provider to start the checkout. A provider that refuses leaves a
     * failed intent behind — the attempt is part of the record either way.
     */
    public function start(PaymentIntent $intent, PaymentGateway $gateway): array
    {
        try {
            return $gateway->initiate($intent);
        } catch (\Throwable $e) {
            $intent->update([
                'status'         => PaymentIntent::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
            ]);

            Log::channel('payments')->error('initiate failed', [
                'reference' => $intent->reference,
                'gateway'   => $gateway->key(),
                'error'     => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle the provider's callback. Returns true only when this call is the
     * one that settled the payment.
     *
     * The whole thing runs under a row lock: two callbacks arriving together
     * (the browser redirect and the provider's IPN, which is normal) both see
     * a consistent status, and only the first gets past the guard.
     */
    public function confirm(Request $request, PaymentIntent $intent, PaymentGateway $gateway): bool
    {
        $confirmed = DB::transaction(function () use ($request, $intent, $gateway) {
            /** @var PaymentIntent $locked */
            $locked = PaymentIntent::whereKey($intent->getKey())->lockForUpdate()->first();

            if (! $locked || ! $locked->isPending()) {
                return false;
            }

            if ($this->hasExpired($locked)) {
                $locked->update([
                    'status'         => PaymentIntent::STATUS_FAILED,
                    'failure_reason' => 'The payment window expired before it was confirmed.',
                ]);

                return false;
            }

            if (! $gateway->verify($request, $locked)) {
                $locked->update(['status' => PaymentIntent::STATUS_FAILED]);

                return false;
            }

            $locked->update([
                'status'  => PaymentIntent::STATUS_PAID,
                'paid_at' => now(),
            ]);

            $this->settle($locked);

            return true;
        });

        $intent->refresh();

        if ($confirmed) {
            Log::channel('payments')->info('payment settled', [
                'reference'   => $intent->reference,
                'gateway'     => $intent->gateway,
                'gateway_ref' => $intent->gateway_ref,
                'amount'      => (float) $intent->amount,
            ]);

            $this->notify($intent);
        }

        return $confirmed;
    }

    /**
     * Mark the payable paid. This is the same write the desk and the
     * record-payment endpoints make, so the invoice, the receipt and the
     * journal entry all come from the observers that already existed —
     * an online payment is not a second accounting path.
     */
    private function settle(PaymentIntent $intent): void
    {
        $payable = $intent->payable;

        if (! $payable) {
            Log::channel('payments')->error('settled an intent whose payable is gone', [
                'reference' => $intent->reference,
            ]);

            return;
        }

        $config = $this->configFor($payable);

        $payable->update([
            'status'         => $config['paid_status'],
            'payment_method' => $intent->method,
        ]);
    }

    private function notify(PaymentIntent $intent): void
    {
        $customer = $intent->customer;

        if (! $customer) {
            return;
        }

        Notification::notify(
            $customer,
            'Payment received',
            'Payment of ' . currency_symbol() . $intent->amount . ' received via ' . $intent->method . '.',
            'payment'
        );
    }

    /** True when the payable cannot be paid online right now, with the reason. */
    public function unpayableReason(Model $payable): ?string
    {
        $config = $this->configFor($payable);
        $status = (string) $payable->status;

        if (strcasecmp($status, (string) $config['paid_status']) === 0) {
            return 'This has already been paid.';
        }

        if (strcasecmp($status, (string) $config['cancelled']) === 0) {
            return 'A cancelled item cannot be paid.';
        }

        if (round((float) $payable->{$config['amount']}, 2) <= 0) {
            return 'This has not been priced yet. The desk will confirm the amount shortly.';
        }

        return null;
    }

    private function hasExpired(PaymentIntent $intent): bool
    {
        $ttl = (int) config('payments.intent_ttl_minutes', 60);

        return $ttl > 0 && $intent->created_at?->addMinutes($ttl)->isPast();
    }

    private function configFor(Model $payable): array
    {
        $config = self::PAYABLES[$payable::class] ?? null;

        if (! $config) {
            throw new \InvalidArgumentException('[' . $payable::class . '] cannot be paid online.');
        }

        return $config;
    }

    /**
     * Unguessable and unique. Providers echo this back as the transaction id,
     * so it must not be a sequential row id an outsider could enumerate.
     */
    private function newReference(): string
    {
        do {
            $reference = 'TRV-' . now()->format('ymd') . '-' . strtoupper(Str::random(10));
        } while (PaymentIntent::where('reference', $reference)->exists());

        return $reference;
    }
}
