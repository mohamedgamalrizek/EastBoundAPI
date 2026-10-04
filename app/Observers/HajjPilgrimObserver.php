<?php

namespace App\Observers;

use App\Models\HajjPilgrim;
use App\Services\Accounting\BillingService;

/**
 * A pilgrim's money used to live only on the row — amount_paid / amount_due
 * typed by hand, nothing in the ledger. Now every save mirrors those columns
 * into an invoice, each instalment gets a receipt, and payment_status is
 * re-derived from the balance instead of being picked from a dropdown.
 */
class HajjPilgrimObserver
{
    public function __construct(protected BillingService $billing) {}

    public function created(HajjPilgrim $pilgrim): void
    {
        // Anchor the committed total: a fresh registration with no money on it
        // owes its package price. Without this, amount_due starts at 0 and the
        // row reads "settled" before a single taka has arrived.
        if ((float) $pilgrim->amount_paid <= 0 && (float) $pilgrim->amount_due <= 0) {
            $price = (float) ($pilgrim->hajjPackage?->price ?? 0);

            if ($price > 0) {
                $pilgrim->amount_due = $price;
                $pilgrim->saveQuietly();
            }
        }
    }

    public function saved(HajjPilgrim $pilgrim): void
    {
        $this->billing->syncHajjPilgrim($pilgrim);

        // Derived, never typed: the badge follows the balance.
        $expected = match (true) {
            (float) $pilgrim->amount_due <= 0 && (float) $pilgrim->amount_paid > 0 => 'Paid',
            (float) $pilgrim->amount_paid > 0                                      => 'Partial',
            default                                                                => 'Pending',
        };

        if ($pilgrim->payment_status !== $expected) {
            $pilgrim->payment_status = $expected;
            $pilgrim->saveQuietly();
        }
    }

    public function deleted(HajjPilgrim $pilgrim): void
    {
        $this->billing->withdrawUnpaidInvoiceFor($pilgrim);
    }
}
