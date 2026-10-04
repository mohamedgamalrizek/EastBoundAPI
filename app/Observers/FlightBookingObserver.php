<?php

namespace App\Observers;

use App\Models\FlightBooking;
use App\Services\Accounting\AgentSettlementService;
use App\Services\Accounting\BillingService;

/**
 * A ticketed flight is a sale and belongs in the books — this observer is what
 * flights were missing: hotel stays and transport trips already posted their
 * invoice and commission on save, flights never did, so a sold ticket had no
 * invoice, no receipt, nothing in the ledger. Doing it here means it holds
 * however the row was saved: admin form, mobile API or seeder.
 *
 * A ticket the desk assigns to an agent earns that agent a commission once it
 * is ticketed (ticket number + fare); cancelling or refunding it withdraws
 * the pending commission again.
 */
class FlightBookingObserver
{
    public function __construct(
        protected BillingService $billing,
        protected AgentSettlementService $settlement,
    ) {}

    public function saved(FlightBooking $ticket): void
    {
        $this->billing->syncFlightBooking($ticket);
        $this->settlement->syncCommission($ticket);
    }

    public function deleted(FlightBooking $ticket): void
    {
        // Money already received stays receipted; only an unpaid invoice
        // follows its ticket out.
        $this->billing->withdrawUnpaidInvoiceFor($ticket);
        $this->settlement->removeCommissionFor($ticket);
    }
}
