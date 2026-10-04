<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Supplier;
use App\Models\SupplierTransaction;

/**
 * Supplier statements, in the books.
 *
 * The supplier ledger already kept a correct running balance — what it never
 * did was reach the accounts, so the agency's costs and what it owed suppliers
 * were invisible outside that one screen.
 *
 *   Bill                     Dr Supplier Costs, Cr Accounts Payable
 *   Payment                  Dr Accounts Payable, Cr Cash-or-Bank
 *   Credit Note / Adjustment Dr Accounts Payable, Cr Supplier Costs
 *
 * A credit note reduces both the cost and what is owed, which is why it sits
 * on the debit side of the statement.
 */
class SupplierAccountingService
{
    /** Payment method -> the account the money left from. */
    public const METHOD_ACCOUNTS = [
        'Cash'  => 'cash',
        'Bank'  => 'bank',
        'bKash' => 'bank',
        'Nagad' => 'bank',
    ];

    public function __construct(protected LedgerService $ledger) {}

    /** Post (or re-post) one statement line. */
    public function post(SupplierTransaction $entry): void
    {
        $amount = $entry->value();

        if ($amount <= 0) {
            $this->ledger->unpostSource($entry);

            return;
        }

        $costs   = Account::system('supplier_cost');
        $payable = Account::system('payable');

        [$account, $contra, $type] = match (true) {
            // A bill: the cost is incurred and the agency now owes it.
            $entry->isBill() => [$costs, $payable, 'expense'],

            // A payment: what is owed goes down and cash goes out.
            $entry->isPayment() => [
                $payable,
                Account::system(self::METHOD_ACCOUNTS[$entry->method] ?? 'bank'),
                'payment',
            ],

            // Credit note or adjustment: owed goes down, and so does the cost.
            default => [$payable, $costs, 'payment'],
        };

        $this->ledger->postForSource($entry, [
            'account_id'        => $account?->id,
            'contra_account_id' => $contra?->id,
            'txn_date'          => $entry->txn_date,
            'account_name'      => $account->name ?? 'Supplier Costs',
            'type'              => $type,
            'amount'            => $amount,
            'reference'         => $entry->reference,
            'description'       => trim(($entry->type ?: 'Supplier entry') . ' — '
                                    . ($entry->supplier->name ?? 'supplier')
                                    . ($entry->description ? ': ' . $entry->description : '')),
        ]);
    }

    /** Drop the journal entry a statement line produced. */
    public function unpost(SupplierTransaction $entry): void
    {
        $this->ledger->unpostSource($entry);
    }

    /**
     * Recompute a supplier's running balance down the statement, then the
     * supplier's own total. Kept here so the repository, the seeders and the
     * rebuild command all use the same rule.
     */
    public function rebuildStatement(int $supplierId): void
    {
        $running = 0.0;

        SupplierTransaction::where('supplier_id', $supplierId)
            ->orderBy('txn_date')->orderBy('id')
            ->get()
            ->each(function (SupplierTransaction $entry) use (&$running) {
                $running += (float) $entry->credit - (float) $entry->debit;
                // saveQuietly: this only rewrites the running balance, and must
                // not re-enter the observer that posts the entry.
                $entry->forceFill(['balance_after' => round($running, 2)])->saveQuietly();
            });

        Supplier::find($supplierId)?->recalculateBalance();
    }

    /** Re-post every supplier line and rebuild every statement. */
    public function rebuildAll(): void
    {
        SupplierTransaction::with('supplier')->chunk(200,
            fn ($rows) => $rows->each(fn (SupplierTransaction $t) => $this->post($t)));

        SupplierTransaction::select('supplier_id')->distinct()->pluck('supplier_id')
            ->filter()
            ->each(fn ($id) => $this->rebuildStatement((int) $id));
    }

    /** What the agency owes suppliers, from the statements themselves. */
    public function totalPayable(): float
    {
        return round((float) SupplierTransaction::sum('credit') - (float) SupplierTransaction::sum('debit'), 2);
    }
}
