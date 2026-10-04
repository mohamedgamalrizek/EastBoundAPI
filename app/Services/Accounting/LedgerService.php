<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Invoice;
use App\Models\Payslip;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The posting engine behind the Accounting module.
 *
 * Everything the reports show is derived here, so the books can only say what
 * the transactions say:
 *   - balances     = opening_balance + (debits - credits), by account type
 *   - auto-posting = an invoice or receipt writes its own journal entry, keyed
 *                    by source, so re-saving updates rather than duplicates
 *   - reconciling  = an invoice's paid amount and status come from its receipts
 */
class LedgerService
{
    /** Method on a receipt -> which system account the money landed in. */
    public const METHOD_ACCOUNTS = [
        'Cash'  => 'cash',
        'Bank'  => 'bank',
        'Card'  => 'bank',
        'bKash' => 'bank',
        'Nagad' => 'bank',
        // Paid out of the customer's wallet: no cash moves now, the agency
        // simply owes them less.
        'Wallet' => 'customer_wallet',
    ];

    /* =====================================================================
     | Balances
     * ================================================================== */

    /**
     * Recompute `accounts.balance` for every account from its opening balance
     * plus every posted leg. Cheap enough to run after any write, and it is the
     * only thing that ever sets that column.
     */
    public function rebuildBalances(): void
    {
        $debits  = $this->legTotals('debit');
        $credits = $this->legTotals('credit');

        foreach (Account::all() as $account) {
            $debit   = (float) ($debits[$account->id] ?? 0);
            $credit  = (float) ($credits[$account->id] ?? 0);
            $balance = (float) $account->opening_balance + $account->normalSign() * ($debit - $credit);

            $account->newQuery()->whereKey($account->id)->update(['balance' => round($balance, 2)]);
        }

        Account::forgetSystemCache();
    }

    /**
     * Total debits (or credits) per account id across both legs.
     *
     * An entry debits its contra account when money comes in (income/receipt)
     * and debits its own account otherwise — so each side is the union of two
     * grouped sums.
     */
    public function legTotals(string $side, $from = null, $to = null): array
    {
        $inbound = AccountTransaction::DEBIT_ON_CONTRA; // income, receipt

        // [column holding this side's account, types that put it there]
        $sources = $side === 'debit'
            ? [['contra_account_id', true], ['account_id', false]]
            : [['account_id', true], ['contra_account_id', false]];

        $totals = [];

        foreach ($sources as [$column, $isInbound]) {
            $rows = AccountTransaction::query()
                ->when($isInbound,
                    fn ($q) => $q->whereIn('type', $inbound),
                    fn ($q) => $q->whereNotIn('type', $inbound))
                ->whereNotNull($column)
                ->between($from, $to)
                ->selectRaw("{$column} as ledger_account_id, SUM(amount) as total")
                ->groupBy($column)
                ->pluck('total', 'ledger_account_id');

            foreach ($rows as $accountId => $sum) {
                $totals[(int) $accountId] = ($totals[(int) $accountId] ?? 0) + (float) $sum;
            }
        }

        return $totals;
    }

    /**
     * An account's balance at the start of $from — its opening balance plus
     * everything posted before that date. Used for ledger/cash book openings.
     */
    public function openingBalance(Account $account, $from = null): float
    {
        $balance = (float) $account->opening_balance;

        if (! $from) {
            return round($balance, 2);
        }

        $debit  = $this->legTotals('debit', null, Carbon::parse($from)->subDay())[$account->id] ?? 0;
        $credit = $this->legTotals('credit', null, Carbon::parse($from)->subDay())[$account->id] ?? 0;

        return round($balance + $account->normalSign() * ($debit - $credit), 2);
    }

    /**
     * Flatten transactions into ledger legs: one row per account touched, with
     * the amount in its debit or credit column. Optionally restricted to a set
     * of accounts (cash book, single-account ledger).
     */
    public function legs($from = null, $to = null, ?array $accountIds = null): Collection
    {
        $query = AccountTransaction::with(['account', 'contraAccount'])
            ->between($from, $to)
            ->orderBy('txn_date')
            ->orderBy('id');

        if ($accountIds !== null) {
            $query->touchingAccounts($accountIds);
        }

        return $query->get()->flatMap(function (AccountTransaction $txn) use ($accountIds) {
            $rows = [];

            foreach (['debit' => $txn->debitAccountId(), 'credit' => $txn->creditAccountId()] as $side => $accountId) {
                if ($accountId === null) {
                    continue; // half-posted legacy entry: nothing to show on this side
                }
                if ($accountIds !== null && ! in_array($accountId, $accountIds, true)) {
                    continue;
                }

                $account  = $txn->account_id === $accountId ? $txn->account : $txn->contraAccount;
                $opposite = $txn->account_id === $accountId ? $txn->contraAccount : $txn->account;

                $rows[] = [
                    'txn'          => $txn,
                    'account_id'   => $accountId,
                    'account'      => $account,
                    'account_name' => $account->name ?? $txn->account_name,
                    'against'      => $opposite->name ?? '—',
                    'side'         => $side,
                    'debit'        => $side === 'debit' ? (float) $txn->amount : 0.0,
                    'credit'       => $side === 'credit' ? (float) $txn->amount : 0.0,
                ];
            }

            return $rows;
        });
    }

    /* =====================================================================
     | Auto-posting from business documents
     * ================================================================== */

    /**
     * Create or update the journal entry belonging to a source document.
     * Keyed by source, so saving the same invoice twice rewrites its entry
     * instead of adding a second one. Returns null when the chart of accounts
     * is missing an account the rule needs — posting a half entry would be
     * worse than posting none.
     */
    public function postForSource(Model $source, array $attributes): ?AccountTransaction
    {
        if (empty($attributes['account_id']) || empty($attributes['contra_account_id'])) {
            return null;
        }

        return AccountTransaction::updateOrCreate(
            ['source_type' => $source->getMorphClass(), 'source_id' => $source->getKey()],
            $attributes
        );
    }

    /**
     * Remove the journal entries a source document produced.
     *
     * Deleted one model at a time on purpose: a mass delete through the query
     * builder fires no model events, so the observer that rebuilds account
     * balances would never run and the balances would drift.
     */
    public function unpostSource(Model $source): void
    {
        $entries = AccountTransaction::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->get();

        $entries->each->delete();

        if ($entries->isEmpty()) {
            return;
        }

        $this->rebuildBalances();
    }

    /**
     * Invoice issued: Dr Accounts Receivable, Cr Sales Revenue.
     *
     * The sale is recognised when the invoice is issued, not when it is paid —
     * the receipt then only moves the money from receivable to cash/bank, so
     * nothing is counted as income twice.
     */
    public function postInvoice(Invoice $invoice): void
    {
        $sales      = Account::system('sales');
        $receivable = Account::system('receivable');

        $this->postForSource($invoice, [
            'account_id'        => $sales?->id,
            'contra_account_id' => $receivable?->id,
            'txn_date'          => $invoice->issue_date,
            'account_name'      => $sales->name ?? 'Sales Revenue',
            'type'              => 'income',
            'amount'            => $invoice->amount,
            'reference'         => $invoice->invoice_no,
            'description'       => 'Invoice ' . $invoice->invoice_no . ' — ' . $invoice->customer_name,
        ]);
    }

    /** Payment received: Dr Cash/Bank (by method), Cr Accounts Receivable. */
    public function postReceipt(Receipt $receipt): void
    {
        $receivable = Account::system('receivable');
        $money      = Account::system(self::METHOD_ACCOUNTS[$receipt->method] ?? 'cash');

        $this->postForSource($receipt, [
            'account_id'        => $receivable?->id,
            'contra_account_id' => $money?->id,
            'txn_date'          => $receipt->received_on,
            'account_name'      => $receivable->name ?? 'Accounts Receivable',
            'type'              => 'receipt',
            'amount'            => $receipt->amount,
            'reference'         => $receipt->receipt_no,
            'description'       => 'Receipt ' . $receipt->receipt_no . ' — ' . $receipt->customer_name
                                    . ' (' . $receipt->method . ')',
        ]);
    }

    /**
     * Salary paid: Dr Salaries, Cr Cash. Only a paid payslip hits the books;
     * an unpaid one is reversed back out if its status changes.
     */
    public function postPayslip(Payslip $payslip): void
    {
        if (strtolower((string) $payslip->status) !== 'paid') {
            $this->unpostSource($payslip);

            return;
        }

        $salaries = Account::system('salary');
        $cash     = Account::system('cash');

        $this->postForSource($payslip, [
            'account_id'        => $salaries?->id,
            'contra_account_id' => $cash?->id,
            // `month` is a 'YYYY-MM' string; salary lands on the month's end.
            'txn_date'          => $this->payslipDate($payslip),
            'account_name'      => $salaries->name ?? 'Salaries',
            'type'              => 'expense',
            'amount'            => $payslip->net_pay ?? 0,
            'reference'         => 'PAY-' . $payslip->id,
            'description'       => 'Payroll ' . $payslip->month . ' — ' . ($payslip->staff_name ?? ('Payslip #' . $payslip->id)),
        ]);
    }

    /** Posting date for a payslip: end of its month, or today if unparseable. */
    protected function payslipDate(Payslip $payslip): string
    {
        try {
            return Carbon::parse($payslip->month . '-01')->endOfMonth()->toDateString();
        } catch (\Throwable $th) {
            return now()->toDateString();
        }
    }

    /* =====================================================================
     | Invoice <-> receipt reconciliation
     * ================================================================== */

    /**
     * Recompute an invoice's paid amount and status from its receipts. Status
     * is never taken from user input, so the list cannot drift out of step with
     * the money actually received.
     */
    public function reconcileInvoice(?Invoice $invoice): void
    {
        if (! $invoice) {
            return;
        }

        $paid     = (float) $invoice->receipts()->sum('amount');
        $refunded = (float) $invoice->refunds()->sum('amount');
        $due      = round((float) $invoice->amount - $paid, 2);

        // Past its due date and still owing money is "overdue", whether or not
        // something has been paid against it — that is what the word means to
        // whoever is chasing the balance.
        $overdue = $invoice->due_date && $invoice->due_date->isBefore(now()->startOfDay());

        $status = match (true) {
            // Everything that was received has been given back: the invoice is
            // settled, but by a refund rather than by payment.
            $paid > 0 && $refunded + 0.009 >= $paid => 'refunded',
            $due <= 0.009 => 'paid',
            $overdue      => 'overdue',
            $paid > 0     => 'partial',
            default       => 'unpaid',
        };

        // Written straight to the table: an Eloquent save here would re-enter
        // the observer that called this method.
        Invoice::withoutEvents(fn () => Invoice::whereKey($invoice->id)->update([
            'paid_amount'     => round($paid, 2),
            'refunded_amount' => round($refunded, 2),
            'status'          => $status,
        ]));
    }

    /** Re-derive every invoice's paid amount/status (used after seeding). */
    public function reconcileAllInvoices(): void
    {
        Invoice::with('receipts')->get()->each(fn (Invoice $invoice) => $this->reconcileInvoice($invoice));
    }

    /**
     * Re-derive the whole ledger from the documents behind it.
     *
     * Used after seeding and by `php artisan accounting:rebuild`. Order
     * matters: documents post first, then invoices reconcile, then balances
     * and agent wallets are recomputed from what was posted.
     */
    public function rebuildAll(): void
    {
        $agents = app(AgentSettlementService::class);

        Account::forgetSystemCache();

        Invoice::chunk(200, fn ($rows) => $rows->each(fn (Invoice $i) => $this->postInvoice($i)));
        Receipt::chunk(200, fn ($rows) => $rows->each(fn (Receipt $r) => $this->postReceipt($r)));
        Payslip::chunk(200, fn ($rows) => $rows->each(fn (Payslip $p) => $this->postPayslip($p)));

        \App\Models\AgentCommission::with('agent')->chunk(200,
            fn ($rows) => $rows->each(fn ($c) => $agents->postCommission($c)));
        \App\Models\AgentWithdrawal::with('agent')->chunk(200,
            fn ($rows) => $rows->each(fn ($w) => $agents->syncWithdrawalPosting($w)));
        \App\Models\AgentInvoice::with('agent')->chunk(200,
            fn ($rows) => $rows->each(fn ($i) => $agents->syncAgentInvoicePosting($i)));

        // Refunds and customer wallets. Wallet lines that belong to a receipt
        // or a refund are skipped inside postEntry — that document books them.
        $billing = app(BillingService::class);
        $wallets = app(CustomerWalletService::class);

        \App\Models\Refund::with('invoice')->chunk(200,
            fn ($rows) => $rows->each(fn ($r) => $billing->postRefund($r)));
        \App\Models\WalletTransaction::with('customer')->chunk(200,
            fn ($rows) => $rows->each(fn ($w) => $wallets->postEntry($w)));

        app(SupplierAccountingService::class)->rebuildAll();

        $this->reconcileAllInvoices();
        $agents->rebuildAllWallets();
        $wallets->rebuildAll();
        $this->rebuildBalances();
    }
}
