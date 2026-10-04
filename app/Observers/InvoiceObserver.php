<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\Accounting\LedgerService;

/**
 * An invoice is a sale, so it belongs in the books the moment it is issued:
 * Dr Accounts Receivable, Cr Sales Revenue. Its status is then re-derived from
 * whatever has been received against it.
 */
class InvoiceObserver
{
    public function __construct(protected LedgerService $ledger) {}

    public function saved(Invoice $invoice): void
    {
        $this->ledger->postInvoice($invoice);
        $this->ledger->reconcileInvoice($invoice);
    }

    public function deleted(Invoice $invoice): void
    {
        $this->ledger->unpostSource($invoice);
    }
}
