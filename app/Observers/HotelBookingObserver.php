<?php

namespace App\Observers;

use App\Models\HotelBooking;
use App\Services\Accounting\AgentSettlementService;
use App\Services\Accounting\BillingService;

/**
 * A confirmed hotel stay is a sale and belongs in the books — invoice on
 * confirmation, receipt when it is marked paid. Doing it here means it holds
 * however the row was saved: admin form, mobile API or seeder. A stay the
 * desk assigns to an agent earns that agent a commission once it is paid.
 */
class HotelBookingObserver
{
    public function __construct(
        protected BillingService $billing,
        protected AgentSettlementService $settlement,
    ) {}

    public function saved(HotelBooking $booking): void
    {
        $this->billing->syncHotelBooking($booking);

        // Status actually changed, so the customer's "I paid" claim has
        // been resolved — clear it via the query builder so this doesn't
        // re-fire saved() and loop.
        if ($booking->wasChanged('status') && $booking->payment_claimed_at) {
            $booking->newQuery()->whereKey($booking->id)->update(['payment_claimed_at' => null]);
            $booking->payment_claimed_at = null;
        }

        $this->settlement->syncCommission($booking);
    }

    public function deleted(HotelBooking $booking): void
    {
        // Money already received stays receipted; only an unpaid invoice
        // follows its booking out.
        $this->billing->withdrawUnpaidInvoiceFor($booking);
        $this->settlement->removeCommissionFor($booking);
    }
}
