<?php

namespace App\Http\Controllers\Api;

use App\Actions\RecordPaymentClaim;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Customer;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;
use App\Traits\StartsOnlinePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Booking payment.
 *
 * Two paths behind one endpoint:
 *   - a live gateway (bKash / SSLCOMMERZ) → a pending payment intent and a
 *     redirect; the provider collects the money and its callback settles the
 *     booking, invoice and receipt included;
 *   - anything else (cash, bank, wallet, or a gateway the agency has not
 *     switched on) → the customer is *declaring* a payment, so it is recorded
 *     as a claim and the desk confirms it.
 *
 * The second path used to mark the booking paid outright, which issued a
 * receipt and created the selling agent's commission on the customer's word
 * alone. The web portal never did that; this now matches it.
 */
class PaymentController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait, ResolvesCustomer, StartsOnlinePayment;

    public function pay(Request $request, $bookingId)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can pay.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'method'    => ['nullable', 'string', 'max:30'], // Cash|Card|bKash|Nagad|Bank
            'reference' => ['nullable', 'string', 'max:100'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $booking = Booking::where('customer_id', $customer->id)->find($bookingId);
        if (! $booking) {
            return $this->responseWithError('Booking not found.', [], 404);
        }
        if ($booking->status === 'paid') {
            return $this->responseWithError('Booking is already paid.', [], 409);
        }
        if ($booking->status === 'cancelled') {
            return $this->responseWithError('Cancelled booking cannot be paid.', [], 409);
        }

        $method = $request->input('method', 'Card');

        // Live gateway? Then the money is collected by the provider and the
        // booking is settled by the callback, not here.
        try {
            if ($checkout = $this->startOnlinePayment($booking, $method, (int) $customer->id)) {
                return $this->responseWithSuccess('Complete the payment to confirm this booking.', $checkout);
            }
        } catch (\RuntimeException $e) {
            return $this->responseWithError($e->getMessage(), [], 502);
        }

        // Wallet still has to cover it, so a customer cannot declare a wallet
        // payment they have no balance for.
        if (strtolower($method) === 'wallet') {
            $method  = 'Wallet';
            $balance = $this->walletBalance($customer->id);
            if ($balance < (float) $booking->amount) {
                return $this->responseWithError(
                    'Insufficient wallet balance. Please top up.', ['balance' => $balance], 422);
            }
        }

        // No gateway: the customer is declaring a payment, not making one. The
        // desk confirms it — same rule the web portal applies, and for the same
        // reason: marking it paid here issues a receipt and creates the selling
        // agent's commission on nothing but the customer's word.
        app(RecordPaymentClaim::class)(
            $booking,
            $this->bookingInfo($booking)['reference'],
            (string) ($booking->customer_name ?: $customer->name),
            (float) $booking->amount,
            $method
        );

        Notification::notify($customer, 'Payment submitted',
            "We have your payment of " . currency_symbol() . "{$booking->amount} for "
                . "booking #{$booking->id} and will confirm it shortly.", 'payment');

        return $this->responseWithSuccess(
            'Payment submitted — we will confirm it shortly and update your booking.',
            [
                'booking'        => $this->bookingInfo($booking->fresh()),
                'awaiting_confirmation' => true,
                'wallet_balance' => $this->walletBalance($customer->id),
            ]
        );
    }

    /**
     * One source of truth for the balance: the same CustomerWalletService the
     * billing layer uses. This controller previously kept its own newest-row
     * lookup (ordered by id, so a back-dated line could go unseen) — the 422
     * guard stays, the duplicate arithmetic does not.
     */
    private function walletBalance($customerId): float
    {
        return app(\App\Services\Accounting\CustomerWalletService::class)->balance((int) $customerId);
    }
}
