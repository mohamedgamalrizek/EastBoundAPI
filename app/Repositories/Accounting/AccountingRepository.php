<?php

namespace App\Repositories\Accounting;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Repositories\BaseRepository;
use App\Repositories\Accounting\AccountingInterface;
use App\Services\Accounting\LedgerService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Chart of accounts CRUD plus every read-only accounting report.
 *
 * All reports are derived from the journal through LedgerService: an account's
 * figure is its opening balance plus the legs posted to it. Nothing here reads
 * a number that was typed in by hand, so the trial balance and the balance
 * sheet balance by construction.
 */
class AccountingRepository extends BaseRepository implements AccountingInterface
{
    /** Allowed option sets — mirror the accounts migration. */
    public const TYPES      = ['Asset', 'Liability', 'Income', 'Expense', 'Equity'];
    public const CASH_TYPES = ['none', 'cash', 'bank'];

    public function __construct(Account $model, protected LedgerService $ledger)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'code'            => $request->code,
            'name'            => $request->name,
            'type'            => $request->type,
            // What the account started at. Its current balance is derived from
            // this plus the journal, and is never written from a form.
            'opening_balance' => $request->opening_balance ?? 0,
            'cash_type'       => in_array($request->cash_type, self::CASH_TYPES, true) ? $request->cash_type : 'none',
        ];
    }

    public function store($request)
    {
        $result = parent::store($request);
        $this->ledger->rebuildBalances();

        return $result;
    }

    public function update($request)
    {
        $result = parent::update($request);
        $this->ledger->rebuildBalances();

        return $result;
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
    }

    public function formData(): array
    {
        return [
            'types'     => self::TYPES,
            'cashTypes' => self::CASH_TYPES,
        ];
    }

    /** An account carrying entries — or one the posting rules need — stays. */
    protected function guardDelete($model): ?string
    {
        $inUse = $model->transactions()->exists() || $model->contraTransactions()->exists();

        if ($inUse || $model->system_key) {
            return ___('alert.record_in_use_cannot_be_deleted');
        }

        return null;
    }

    /* =====================================================================
     | Reports
     * ================================================================== */

    public function dashboard(array $filters = [])
    {
        [$from, $to] = $this->period($filters);

        $income  = $this->accountRows(['Income'], $from, $to);
        $expense = $this->accountRows(['Expense'], $from, $to);

        $totalIncome  = $income->sum('net');
        $totalExpense = $expense->sum('net');

        [$months, $incomeSeries, $expenseSeries] = $this->monthlySeries($from, $to);

        return array_merge($this->filterBag($from, $to), [
            'income'        => $totalIncome,
            'expense'       => $totalExpense,
            'profit'        => $totalIncome - $totalExpense,
            // Cash in hand + at bank, taken from the accounts flagged as such
            // rather than from an account that happens to be named "Cash".
            'cash'          => (float) Account::whereIn('cash_type', ['cash', 'bank'])->sum('balance'),
            'receivable'    => (float) (Account::system('receivable')?->balance ?? 0),
            'months'        => $months,
            'incomeSeries'  => $incomeSeries,
            'expenseSeries' => $expenseSeries,
            'expenseSplit'  => $expense->where('net', '>', 0)->pluck('net', 'name'),
        ]);
    }

    /** Legs landing on an Income account — what the agency actually earned. */
    public function income(array $filters = [])
    {
        [$from, $to] = $this->period($filters);

        return array_merge($this->filterBag($from, $to), [
            'rows'  => $this->legRowsForTypes(['Income'], $from, $to),
            'total' => $this->accountRows(['Income'], $from, $to)->sum('net'),
        ]);
    }

    public function expenses(array $filters = [])
    {
        [$from, $to] = $this->period($filters);

        return array_merge($this->filterBag($from, $to), [
            'rows'  => $this->legRowsForTypes(['Expense'], $from, $to),
            'total' => $this->accountRows(['Expense'], $from, $to)->sum('net'),
        ]);
    }

    /** The journal proper: every entry with both of its legs named. */
    public function journal(array $filters = [])
    {
        [$from, $to] = $this->period($filters);

        $entries = AccountTransaction::with(['account', 'contraAccount'])
            ->between($from, $to)
            ->orderByDesc('txn_date')->orderByDesc('id')
            ->get();

        return array_merge($this->filterBag($from, $to), [
            'entries' => $entries,
            'total'   => (float) $entries->sum('amount'),
        ]);
    }

    public function cashbook(array $filters = [])
    {
        return array_merge($this->book('cash', $filters), ['book' => 'cash']);
    }

    public function bankbook(array $filters = [])
    {
        return array_merge($this->book('bank', $filters), ['book' => 'bank']);
    }

    /**
     * Cash Book / Bank Book. Only the accounts flagged with that cash type are
     * included, so the two pages no longer show the same rows.
     */
    protected function book(string $cashType, array $filters): array
    {
        [$from, $to] = $this->period($filters);

        $accounts = Account::where('cash_type', $cashType)->orderBy('code')->get();
        $ids      = $accounts->pluck('id')->all();

        $opening = $accounts->sum(fn (Account $a) => $this->ledger->openingBalance($a, $from));
        $balance = $opening;

        $rows = $ids
            ? $this->ledger->legs($from, $to, $ids)->map(function (array $leg) use (&$balance) {
                // These are asset accounts: a debit is money in.
                $balance += $leg['debit'] - $leg['credit'];

                return $leg + ['balance' => $balance];
            })->values()
            : collect();

        return array_merge($this->filterBag($from, $to), [
            'accounts'   => $accounts,
            'rows'       => $rows,
            'opening'    => $opening,
            'closing'    => $balance,
            'totalIn'    => $rows->sum('debit'),
            'totalOut'   => $rows->sum('credit'),
        ]);
    }

    /**
     * Ledger: entries grouped per account with an opening balance and a running
     * balance, optionally narrowed to one account.
     */
    public function ledger(array $filters = [])
    {
        [$from, $to] = $this->period($filters);

        $accountId = $filters['account_id'] ?? null;
        $accounts  = Account::orderBy('code')->get();
        $selected  = $accountId ? $accounts->firstWhere('id', (int) $accountId) : null;

        $shown = $selected ? collect([$selected]) : $accounts;
        $legs  = $this->ledger->legs($from, $to, $shown->pluck('id')->all())->groupBy('account_id');

        $groups = $shown->map(function (Account $account) use ($legs, $from) {
            $opening = $this->ledger->openingBalance($account, $from);
            $balance = $opening;

            $rows = ($legs[$account->id] ?? collect())->map(function (array $leg) use (&$balance, $account) {
                $balance += $account->normalSign() * ($leg['debit'] - $leg['credit']);

                return $leg + ['balance' => $balance];
            })->values();

            return [
                'account' => $account,
                'opening' => $opening,
                'rows'    => $rows,
                'debit'   => $rows->sum('debit'),
                'credit'  => $rows->sum('credit'),
                'closing' => $balance,
            ];
        })->filter(fn ($g) => $g['rows']->isNotEmpty() || abs($g['opening']) > 0.009)->values();

        return array_merge($this->filterBag($from, $to), [
            'groups'   => $groups,
            'accounts' => $accounts,
            'selected' => $selected,
        ]);
    }

    /**
     * Trial balance: opening, period movement and closing per account, with the
     * totals that must agree. `balanced` is shown on the page — a report that
     * cannot prove itself is worth nothing.
     */
    public function trial(array $filters = [])
    {
        [$from, $to] = $this->period($filters);

        $debits  = $this->ledger->legTotals('debit', $from, $to);
        $credits = $this->ledger->legTotals('credit', $from, $to);

        $rows = Account::orderBy('code')->get()->map(function (Account $account) use ($debits, $credits, $from) {
            $debit   = (float) ($debits[$account->id] ?? 0);
            $credit  = (float) ($credits[$account->id] ?? 0);
            $opening = $this->ledger->openingBalance($account, $from);
            $closing = $opening + $account->normalSign() * ($debit - $credit);

            return [
                'account'   => $account,
                'opening'   => $opening,
                'debit'     => $debit,
                'credit'    => $credit,
                'closing'   => $closing,
                // A debit-normal account with a negative balance really sits on
                // the credit side, so it is reported there.
                'is_debit'  => $account->isDebitNormal() ? $closing >= 0 : $closing < 0,
            ];
        });

        $totalDebit  = $rows->filter(fn ($r) => $r['is_debit'])->sum(fn ($r) => abs($r['closing']));
        $totalCredit = $rows->reject(fn ($r) => $r['is_debit'])->sum(fn ($r) => abs($r['closing']));

        return array_merge($this->filterBag($from, $to), [
            'rows'            => $rows,
            'totalDebit'      => $totalDebit,
            'totalCredit'     => $totalCredit,
            'movementDebit'   => array_sum($debits),
            'movementCredit'  => array_sum($credits),
            'difference'      => round($totalDebit - $totalCredit, 2),
            'balanced'        => abs($totalDebit - $totalCredit) < 0.01,
        ]);
    }

    public function pl(array $filters = [])
    {
        [$from, $to] = $this->period($filters);

        $incomeRows  = $this->accountRows(['Income'], $from, $to);
        $expenseRows = $this->accountRows(['Expense'], $from, $to);

        $totalIncome  = $incomeRows->sum('net');
        $totalExpense = $expenseRows->sum('net');

        return array_merge($this->filterBag($from, $to), [
            'incomeRows'   => $incomeRows,
            'expenseRows'  => $expenseRows,
            'totalIncome'  => $totalIncome,
            'totalExpense' => $totalExpense,
            'netProfit'    => $totalIncome - $totalExpense,
        ]);
    }

    /**
     * Balance sheet as at the end of the period. The profit earned so far is
     * shown under equity — without it the two sides cannot meet, which is why
     * the old page never balanced.
     */
    public function balance(array $filters = [])
    {
        [, $to] = $this->period($filters);

        $closing = fn (array $types) => $this->accountRows($types, null, $to, false);

        $assets      = $closing(['Asset']);
        $liabilities = $closing(['Liability']);
        $equity      = $closing(['Equity']);

        $earnings = $this->accountRows(['Income'], null, $to)->sum('net')
                  - $this->accountRows(['Expense'], null, $to)->sum('net');

        $totalAssets    = $assets->sum('balance');
        $totalLiabEquity = $liabilities->sum('balance') + $equity->sum('balance') + $earnings;

        return array_merge($this->filterBag(null, $to), [
            'assets'          => $assets,
            'liabilities'     => $liabilities,
            'equity'          => $equity,
            'earnings'        => $earnings,
            'totalAssets'     => $totalAssets,
            'totalLiabEquity' => $totalLiabEquity,
            'difference'      => round($totalAssets - $totalLiabEquity, 2),
            'balanced'        => abs($totalAssets - $totalLiabEquity) < 0.01,
        ]);
    }

    /** Money given back, taken from the Refunds account rather than a name. */
    public function refunds(array $filters = [])
    {
        [$from, $to] = $this->period($filters);

        $refundAccount = Account::system('refund');

        $entries = $refundAccount
            ? AccountTransaction::with(['account', 'contraAccount'])
                ->where(fn ($q) => $q->where('account_id', $refundAccount->id)
                                     ->orWhere('contra_account_id', $refundAccount->id))
                ->between($from, $to)
                ->orderByDesc('txn_date')->orderByDesc('id')
                ->get()
            : collect();

        return array_merge($this->filterBag($from, $to), [
            'entries' => $entries,
            'total'   => (float) $entries->sum('amount'),
            'account' => $refundAccount,
        ]);
    }

    /**
     * VAT/AIT on taxable sales. Rates come from Settings (vat_rate, ait_rate)
     * so they can be changed when the law changes, instead of living in code.
     */
    public function tax(array $filters = [])
    {
        [$from, $to] = $this->period($filters);

        $vatRate = (float) (settings('vat_rate') ?: 15);
        $aitRate = (float) (settings('ait_rate') ?: 5);

        $sales = $this->accountRows(['Income'], $from, $to)->sum('net');

        $periods = $this->legRowsForTypes(['Income'], $from, $to)
            ->groupBy(fn (array $leg) => $leg['txn']->txn_date?->format('Y-m'))
            ->map(function (Collection $legs, $ym) use ($vatRate, $aitRate) {
                $net = $legs->sum('credit') - $legs->sum('debit');

                return [
                    'period' => $ym ? Carbon::parse($ym . '-01')->format('M Y') : '—',
                    'sales'  => $net,
                    'vat'    => $net * $vatRate / 100,
                    'ait'    => $net * $aitRate / 100,
                ];
            })
            ->sortKeysDesc()
            ->values();

        return array_merge($this->filterBag($from, $to), [
            'vatRate'      => $vatRate,
            'aitRate'      => $aitRate,
            'taxableSales' => $sales,
            'vatCollected' => $sales * $vatRate / 100,
            'vatPayable'   => (float) (Account::system('vat')?->balance ?? 0),
            'ait'          => $sales * $aitRate / 100,
            'netTax'       => $sales * ($vatRate + $aitRate) / 100,
            'periods'      => $periods,
        ]);
    }

    /* =====================================================================
     | Shared helpers
     * ================================================================== */

    /** Read the from/to filters; both are optional and default to all time. */
    protected function period(array $filters): array
    {
        $clean = fn ($value) => filled($value) ? Carbon::parse($value)->toDateString() : null;

        return [$clean($filters['from'] ?? null), $clean($filters['to'] ?? null)];
    }

    /** Filter values echoed back so the page can re-render its date inputs. */
    protected function filterBag($from, $to): array
    {
        return ['from' => $from, 'to' => $to];
    }

    /**
     * Per-account movement for the given account types.
     *
     * $movementOnly (default) returns what the period moved — used by P&L and
     * the dashboard. With it off, the row carries the closing balance instead,
     * which is what a balance sheet needs.
     */
    protected function accountRows(array $types, $from, $to, bool $movementOnly = true): Collection
    {
        $debits  = $this->ledger->legTotals('debit', $from, $to);
        $credits = $this->ledger->legTotals('credit', $from, $to);

        return Account::whereIn('type', $types)->orderBy('code')->get()
            ->map(function (Account $account) use ($debits, $credits, $from, $movementOnly) {
                $debit  = (float) ($debits[$account->id] ?? 0);
                $credit = (float) ($credits[$account->id] ?? 0);
                $net    = $account->normalSign() * ($debit - $credit);

                return [
                    'account' => $account,
                    'name'    => $account->name,
                    'code'    => $account->code,
                    'debit'   => $debit,
                    'credit'  => $credit,
                    'net'     => $net,
                    'balance' => $this->ledger->openingBalance($account, $from) + $net,
                ];
            })
            ->filter(fn (array $row) => $movementOnly
                ? abs($row['net']) > 0.009
                : abs($row['balance']) > 0.009)
            ->values();
    }

    /** Ledger legs that landed on accounts of the given types. */
    protected function legRowsForTypes(array $types, $from, $to): Collection
    {
        $ids = Account::whereIn('type', $types)->pluck('id')->all();

        return $ids ? $this->ledger->legs($from, $to, $ids) : collect();
    }

    /** Income vs expense by month, for the dashboard chart. */
    protected function monthlySeries($from, $to): array
    {
        $income  = $this->legRowsForTypes(['Income'], $from, $to);
        $expense = $this->legRowsForTypes(['Expense'], $from, $to);

        $byMonth = fn (Collection $legs, int $sign) => $legs
            ->groupBy(fn (array $leg) => $leg['txn']->txn_date?->format('Y-m'))
            ->map(fn (Collection $rows) => $sign * ($rows->sum('debit') - $rows->sum('credit')));

        $incomeByMonth  = $byMonth($income, -1);   // income is credit-normal
        $expenseByMonth = $byMonth($expense, 1);

        $months = $incomeByMonth->keys()->merge($expenseByMonth->keys())
            ->filter()->unique()->sort()->values();

        return [
            $months->map(fn ($m) => Carbon::parse($m . '-01')->format('M Y'))->values(),
            $months->map(fn ($m) => round((float) ($incomeByMonth[$m] ?? 0), 2))->values(),
            $months->map(fn ($m) => round((float) ($expenseByMonth[$m] ?? 0), 2))->values(),
        ];
    }
}
