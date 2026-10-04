<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\Accounting\AgentSettlementService;
use App\Services\Accounting\BillingService;
use App\Services\Booking\BookingPricing;
use App\Services\LoyaltyService;
use App\Services\WhatsAppService;

/**
 * A paid booking that belongs to an agent earns that agent a commission, at
 * their own rate. Doing it here means it holds however the booking was saved —
 * admin form, mobile API or importer — instead of waiting for somebody to
 * remember to key the commission in.
 */
class BookingObserver
{
    public function __construct(
        protected AgentSettlementService $settlement,
        protected BillingService $billing,
        protected LoyaltyService $loyalty,
        protected WhatsAppService $whatsapp,
        protected BookingPricing $pricing,
    ) {}

    /**
     * Every booking carries a list price, whether or not anything was
     * discounted off it.
     *
     * Only the self-service paths quote through BookingPricing; the
     * back-office form, an agent's sale, an imported row and the seeders all
     * write `amount` directly. Filling the gross in here means no caller has
     * to remember, and a booking's saving can always be read as
     * gross - amount rather than sometimes being 0 - amount.
     */
    public function creating(Booking $booking): void
    {
        if ((float) $booking->gross_amount <= 0) {
            $booking->gross_amount = $booking->amount;
        }
    }

    public function saved(Booking $booking): void
    {
        // Billing first: the sale reaches the books (invoice, and a receipt
        // once it is paid), and only then is the agent's cut worked out.
        $this->billing->syncBooking($booking);

        $this->clearPaymentClaim($booking);
        $this->syncDiscounts($booking);
        $this->loyalty->awardPaidBooking($booking);
        if ($booking->wasChanged('status') && $booking->customer?->phone) {
            try { $this->whatsapp->send($booking->customer->phone, "FLOW booking #{$booking->id} is now {$booking->status}."); } catch (\Throwable $e) { report($e); }
        }
    }

    /**
     * Once an admin has acted on the booking (status actually changed), the
     * customer's "I paid" claim has been resolved one way or another — drop
     * it so the desk isn't left chasing a claim that is no longer open.
     *
     * Updated via the query builder, not save(), so this doesn't re-fire
     * saved() and loop.
     */
    private function clearPaymentClaim(Booking $booking): void
    {
        if ($booking->wasChanged('status') && $booking->payment_claimed_at) {
            $booking->newQuery()->whereKey($booking->id)->update(['payment_claimed_at' => null]);
            $booking->payment_claimed_at = null;
        }
        $this->settlement->syncCommission($booking);
    }

    /**
     * A cancelled booking hands its discounts back: the coupon use is
     * released and the points return to the customer's balance. Doing it here
     * rather than in each cancel action means the back office, the portal and
     * the app all behave the same.
     *
     * Un-cancelling takes them again, so the pair stays symmetric however
     * many times a booking is flipped — releasing on every save of an already
     * cancelled booking would hand back a use per save.
     */
    private function syncDiscounts(Booking $booking): void
    {
        if (! $booking->wasChanged('status')) {
            return;
        }

        $was = strtolower((string) $booking->getOriginal('status'));
        $now = strtolower((string) $booking->status);

        if ($now === 'cancelled' && $was !== 'cancelled') {
            $this->pricing->release($booking);
        } elseif ($was === 'cancelled' && $now !== 'cancelled') {
            $this->pricing->reclaim($booking);
        }
    }

    public function deleted(Booking $booking): void
    {
        // An approved commission is already in the agent's wallet and is left
        // alone; only one that was still pending goes with the booking.
        $this->settlement->removeCommissionFor($booking);
    }
}
