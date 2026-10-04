<?php

namespace App\Observers;

use App\Models\AccountTransaction;
use App\Services\Accounting\LedgerService;

/**
 * Keeps `accounts.balance` derived. Every write to the journal — by hand, by
 * the seeders, or auto-posted from an invoice — re-runs the balance rebuild,
 * so no page ever reads a balance the transactions do not support.
 */
class AccountTransactionObserver
{
    public function __construct(protected LedgerService $ledger) {}

    public function saved(AccountTransaction $transaction): void
    {
        $this->ledger->rebuildBalances();
    }

    public function deleted(AccountTransaction $transaction): void
    {
        $this->ledger->rebuildBalances();
    }
}
