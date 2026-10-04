<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

/**
 * Chart of accounts.
 *
 * Two things here matter beyond the names:
 *
 *  - `system_key` is how the posting rules find an account (an invoice debits
 *    'receivable' and credits 'sales'). Renaming an account is then harmless;
 *    matching on the English name was not.
 *  - opening balances are a balanced set: assets 15,700,000 = liabilities
 *    1,210,000 + equity 14,490,000. Income and expense accounts open at zero —
 *    they are period accounts, and everything in them comes from the journal.
 */
class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // code, name, type, opening_balance, system_key, cash_type
        $rows = [
            ['1000', 'Cash',                  'Asset',     4200000,  'cash',       'cash'],
            ['1100', 'Bank — City Bank',      'Asset',     11500000, 'bank',       'bank'],
            // Receivables are created by invoices, so this starts empty.
            ['1200', 'Accounts Receivable',   'Asset',     0,        'receivable', 'none'],
            // What suppliers are owed is posted from their statements, so
            // this opens empty and always matches the supplier ledger.
            ['2000', 'Accounts Payable',      'Liability', 0,        'payable',    'none'],
            ['2100', 'VAT Payable',           'Liability', 1210000,  'vat',        'none'],
            // What the agency owes its agents: raised when a commission is
            // earned, cleared when a payout is paid.
            ['2200', 'Agent Payable',         'Liability', 0,        'agent_payable', 'none'],
            // Customer wallet balances: money held on account, owed back to
            // the customer until they spend it.
            ['2300', 'Customer Wallet',       'Liability', 0,        'customer_wallet', 'none'],
            ['3000', 'Owner Equity',          'Equity',    8000000,  null,         'none'],
            ['3100', 'Retained Earnings',     'Equity',    6490000,  'retained',   'none'],
            ['4000', 'Sales Revenue',         'Income',    0,        'sales',      'none'],
            ['4100', 'Visa & Other Income',   'Income',    0,        null,         'none'],
            ['5000', 'Salaries',              'Expense',   0,        'salary',     'none'],
            ['5100', 'Supplier Costs',        'Expense',   0,        'supplier_cost', 'none'],
            ['5200', 'Marketing & Office',    'Expense',   0,        null,         'none'],
            ['5300', 'Refunds',               'Expense',   0,        'refund',     'none'],
            ['5400', 'Agent Commission',      'Expense',   0,        'commission', 'none'],
        ];

        foreach ($rows as $r) {
            Account::updateOrCreate(
                ['code' => $r[0]],
                [
                    'name'            => $r[1],
                    'type'            => $r[2],
                    'opening_balance' => $r[3],
                    'system_key'      => $r[4],
                    'cash_type'       => $r[5],
                    // Recomputed from the journal by LedgerService; seeded here
                    // only so a fresh row is not left at NULL.
                    'balance'         => $r[3],
                ]
            );
        }

        Account::forgetSystemCache();
    }
}
