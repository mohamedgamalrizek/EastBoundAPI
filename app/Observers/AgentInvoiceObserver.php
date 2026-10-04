<?php

namespace App\Observers;

use App\Models\AgentInvoice;
use App\Services\Accounting\AgentSettlementService;

/**
 * "Paid" on an agent invoice used to be a word in a dropdown: no receipt, no
 * ledger entry, no trace in the agent's wallet. Like a payslip, the status is
 * still typed — but flipping it to paid now moves the money (Dr wallet or
 * cash/bank, Cr Sales) and flipping it back reverses it.
 */
class AgentInvoiceObserver
{
    public function __construct(protected AgentSettlementService $settlement) {}

    public function saved(AgentInvoice $invoice): void
    {
        $this->settlement->syncAgentInvoicePosting($invoice);
    }

    public function deleted(AgentInvoice $invoice): void
    {
        $this->settlement->removeWalletEntry($invoice);
        app(\App\Services\Accounting\LedgerService::class)->unpostSource($invoice);
    }
}
