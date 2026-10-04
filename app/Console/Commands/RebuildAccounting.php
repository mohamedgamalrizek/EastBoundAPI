<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Invoice;
use App\Models\Payslip;
use App\Models\Receipt;
use App\Services\Accounting\LedgerService;
use Illuminate\Console\Command;

/**
 * Re-derives the whole ledger from the documents behind it: re-posts every
 * invoice, receipt and paid payslip, re-reconciles the invoices, then rebuilds
 * account balances. Safe to run repeatedly — posting is keyed by source, so it
 * updates entries instead of duplicating them.
 *
 * Run it after migrating an existing database, or any time the books need to
 * be checked against their sources.
 */
class RebuildAccounting extends Command
{
    protected $signature = 'accounting:rebuild';

    protected $description = 'Re-post invoices/receipts/payslips and rebuild account balances';

    public function handle(LedgerService $ledger): int
    {
        $this->line('Re-posting invoices, receipts, payslips, commissions and payouts…');
        $ledger->rebuildAll();

        $this->report($ledger);

        return self::SUCCESS;
    }

    /** Print the trial-balance check so a rebuild proves itself. */
    private function report(LedgerService $ledger): void
    {
        $wallets = \App\Models\AgentWalletTransaction::selectRaw('agent_id, COUNT(*) as wallet_lines')
            ->groupBy('agent_id')->get();
        $debits  = $ledger->legTotals('debit');
        $credits = $ledger->legTotals('credit');

        $totalDebit  = array_sum($debits);
        $totalCredit = array_sum($credits);

        $unposted = AccountTransaction::whereNull('contra_account_id')->count();

        $this->newLine();
        $this->table(['Check', 'Value'], [
            ['Entries',        AccountTransaction::count()],
            ['Total debits',   number_format($totalDebit, 2)],
            ['Total credits',  number_format($totalCredit, 2)],
            ['Balanced',       abs($totalDebit - $totalCredit) < 0.01 ? 'yes' : 'NO'],
            ['Single-legged',  $unposted],
            ['Agent wallets',   $wallets->count() . ' agent(s), ' . $wallets->sum('wallet_lines') . ' line(s)'],
            ['Agent payable',   number_format((float) (Account::system('agent_payable')?->balance ?? 0), 2)],
        ]);

        if (abs($totalDebit - $totalCredit) >= 0.01 || $unposted > 0) {
            $this->warn('Some entries are missing their second leg — those cannot balance.');
        }
    }
}
