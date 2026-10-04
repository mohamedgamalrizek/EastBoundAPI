<?php

namespace App\Observers;

use App\Models\WalletTransaction;
use App\Services\Accounting\CustomerWalletService;
use App\Services\Accounting\LedgerService;

/**
 * Customer wallet lines. A top-up is real money arriving that the agency now
 * owes back, so it is booked (Dr Cash/Bank, Cr Customer Wallet). Lines that
 * belong to a receipt or a refund are posted by that document instead — see
 * CustomerWalletService::postEntry.
 */
class WalletTransactionObserver
{
    public function __construct(protected CustomerWalletService $wallet, protected LedgerService $ledger) {}

    public function saved(WalletTransaction $entry): void
    {
        $this->wallet->postEntry($entry);
    }

    public function deleted(WalletTransaction $entry): void
    {
        $this->ledger->unpostSource($entry);

        if ($entry->customer_id) {
            $this->wallet->rebuild((int) $entry->customer_id);
        }
    }
}
