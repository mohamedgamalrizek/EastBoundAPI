<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\Receipt;
use App\Services\Accounting\LedgerService;

/**
 * A receipt moves money from receivable into cash/bank (Dr Cash, Cr AR) and is
 * the only thing that marks an invoice paid. When a receipt is moved to another
 * invoice, both the old and the new one are re-derived.
 */
class ReceiptObserver
{
    public function __construct(protected LedgerService $ledger) {}

    public function saved(Receipt $receipt): void
    {
        $this->ledger->postReceipt($receipt);

        // Paid from the wallet: the wallet has to go down by the same amount.
        app(\App\Services\Accounting\BillingService::class)->syncReceiptWallet($receipt);

        $this->ledger->reconcileInvoice($receipt->invoice()->first());

        // Receipt re-pointed at a different invoice: the old one is no longer
        // paid by it and has to be re-derived too.
        $previousId = $receipt->getOriginal('invoice_id');
        if ($previousId && $previousId !== $receipt->invoice_id) {
            $this->ledger->reconcileInvoice(Invoice::find($previousId));
        }
    }

    public function deleted(Receipt $receipt): void
    {
        $this->ledger->unpostSource($receipt);
        app(\App\Services\Accounting\CustomerWalletService::class)->removeEntryFor($receipt);
        $this->ledger->reconcileInvoice($receipt->invoice()->first());
    }
}
