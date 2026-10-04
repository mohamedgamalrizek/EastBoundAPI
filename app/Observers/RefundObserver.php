<?php

namespace App\Observers;

use App\Models\Refund;
use App\Services\Accounting\BillingService;
use App\Services\Accounting\LedgerService;

/**
 * A refund posts itself (Dr Refunds, Cr cash/bank/wallet), puts the money in
 * the customer's wallet when that is where it went, and re-derives the
 * invoice — which is how an invoice comes to read "refunded" instead of
 * silently staying "paid" after the customer got their money back.
 */
class RefundObserver
{
    public function __construct(protected BillingService $billing, protected LedgerService $ledger) {}

    public function saved(Refund $refund): void
    {
        $this->billing->postRefund($refund);
    }

    public function deleted(Refund $refund): void
    {
        $this->ledger->unpostSource($refund);
        app(\App\Services\Accounting\CustomerWalletService::class)->removeEntryFor($refund);
        $this->ledger->reconcileInvoice($refund->invoice()->first());
    }
}
