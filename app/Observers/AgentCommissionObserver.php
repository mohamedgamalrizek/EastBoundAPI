<?php

namespace App\Observers;

use App\Models\AgentCommission;
use App\Services\Accounting\AgentSettlementService;

/**
 * A commission is a cost the agency has already incurred, so it reaches the
 * books when it is earned: Dr Agent Commission, Cr Agent Payable. Approving it
 * moves it into the agent's wallet; the payable is only cleared when a
 * withdrawal is paid.
 */
class AgentCommissionObserver
{
    public function __construct(protected AgentSettlementService $settlement) {}

    public function saved(AgentCommission $commission): void
    {
        $this->settlement->postCommission($commission);

        // Keep the wallet in step with the status, whichever screen changed it.
        if ($commission->isApproved()) {
            $this->settlement->walletEntry($commission, 'credit', $commission->amount,
                $commission->approved_on ?? now()->toDateString(),
                'Commission ' . $commission->reference . ' credited');
        } else {
            $this->settlement->removeWalletEntry($commission);
        }
    }

    public function deleted(AgentCommission $commission): void
    {
        $this->settlement->removeWalletEntry($commission);
        app(\App\Services\Accounting\LedgerService::class)->unpostSource($commission);
    }
}
