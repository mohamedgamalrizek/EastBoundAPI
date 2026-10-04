<?php

namespace App\Observers;

use App\Models\SupplierTransaction;
use App\Services\Accounting\SupplierAccountingService;

/**
 * Supplier bills and payments post themselves, so what the agency owes its
 * suppliers and what it spends with them show up in the accounts rather than
 * only on the supplier's own statement.
 */
class SupplierTransactionObserver
{
    public function __construct(protected SupplierAccountingService $suppliers) {}

    public function saved(SupplierTransaction $entry): void
    {
        $this->suppliers->post($entry);
    }

    public function deleted(SupplierTransaction $entry): void
    {
        $this->suppliers->unpost($entry);
    }
}
