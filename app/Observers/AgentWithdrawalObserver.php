<?php

namespace App\Observers;

use App\Models\AgentWithdrawal;
use App\Services\Accounting\AgentSettlementService;

/**
 * The wallet debit and the cash payment follow the withdrawal's status, so a
 * payout can never be recorded in one place and missed in the other — and
 * flipping it back off 'paid' reverses both.
 */
class AgentWithdrawalObserver
{
    public function __construct(protected AgentSettlementService $settlement) {}

    public function saved(AgentWithdrawal $withdrawal): void
    {
        $this->settlement->syncWithdrawalPosting($withdrawal);
    }

    public function deleted(AgentWithdrawal $withdrawal): void
    {
        $this->settlement->removeWalletEntry($withdrawal);
        app(\App\Services\Accounting\LedgerService::class)->unpostSource($withdrawal);
    }
}
