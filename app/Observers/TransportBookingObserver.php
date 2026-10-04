<?php

namespace App\Observers;

use App\Models\TransportBooking;
use App\Services\Accounting\AgentSettlementService;
use App\Services\Accounting\BillingService;

/**
 * A confirmed (or completed, or paid) transport trip reaches the books the
 * same way a tour booking does. A pending app request with fare 0 posts
 * nothing — it is priced by the agency on confirmation, and only a paid trip
 * earns its selling agent a commission.
 */
class TransportBookingObserver
{
    public function __construct(
        protected BillingService $billing,
        protected AgentSettlementService $settlement,
    ) {}

    public function saved(TransportBooking $trip): void
    {
        $this->billing->syncTransportBooking($trip);

        // Status actually changed, so the customer's "I paid" claim has
        // been resolved — clear it via the query builder so this doesn't
        // re-fire saved() and loop.
        if ($trip->wasChanged('status') && $trip->payment_claimed_at) {
            $trip->newQuery()->whereKey($trip->id)->update(['payment_claimed_at' => null]);
            $trip->payment_claimed_at = null;
        }
        $this->settlement->syncCommission($trip);
    }

    public function deleted(TransportBooking $trip): void
    {
        $this->billing->withdrawUnpaidInvoiceFor($trip);
        $this->settlement->removeCommissionFor($trip);
    }
}
