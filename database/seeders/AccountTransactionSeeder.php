<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\AccountTransaction;

/**
 * Manual journal entries — the ones no document produces on its own (office
 * rent, supplier payments, refunds).
 *
 * Sales are deliberately absent: invoices post their own income now, and
 * seeding them here as well would count every sale twice.
 */
class AccountTransactionSeeder extends Seeder
{
    /**
     * References this seeder used to create before invoices posted themselves.
     * Removed on re-seed so an upgraded database does not keep counting the
     * same sale twice.
     */
    private const RETIRED = ['BKG-10241', 'BKG-10240', 'BKG-10239', 'BKG-10238', 'BKG-10236', 'PAY-06'];

    public function run(): void
    {
        $byCode = Account::pluck('id', 'code');

        if ($byCode->isEmpty()) {
            return; // accounts must be seeded first
        }

        AccountTransaction::whereNull('source_type')->whereIn('reference', self::RETIRED)->delete();

        // txn_date, account code, contra code, type, amount, reference, description
        $rows = [
            // Income the agency takes without raising an invoice.
            ['2026-06-03', '4100', '1000', 'income',  9000,   'VISA-3301', 'Visa service fee — walk-in'],
            ['2026-05-30', '4100', '1100', 'income',  27000,  'VISA-3297', 'Umrah visa service — bank transfer'],

            // Operating costs, paid out of cash or bank.
            ['2026-06-04', '5100', '1100', 'expense', 86000,  'SUP-110',   'Supplier payment — hotel vendor'],
            ['2026-06-02', '5200', '1100', 'expense', 45000,  'FB-Ads',    'Marketing — ad spend'],
            ['2026-06-04', '5200', '1000', 'expense', 120000, 'RENT-06',   'Office rent — June'],
            ['2026-06-01', '5100', '1100', 'expense', 210000, 'SUP-109',   'Supplier payment — airline'],
            ['2026-05-30', '5200', '1000', 'expense', 32000,  'UTIL-06',   'Utilities & internet'],
            ['2026-05-28', '5100', '1100', 'expense', 154000, 'SUP-108',   'Supplier payment — transport'],

            // Refunds: posted to the Refunds account, not guessed from the
            // reference text.
            ['2026-06-05', '5300', '1000', 'expense', 27000,  'RFD-220',   'Refund — BKG-10236'],
            ['2026-06-02', '5300', '1000', 'expense', 18500,  'RFD-219',   'Refund — BKG-10228'],
        ];

        foreach ($rows as $r) {
            if (! isset($byCode[$r[1]], $byCode[$r[2]])) {
                continue;
            }

            AccountTransaction::updateOrCreate(
                ['reference' => $r[5], 'source_type' => null],
                [
                    'account_id'        => $byCode[$r[1]],
                    'contra_account_id' => $byCode[$r[2]],
                    'txn_date'          => $r[0],
                    'account_name'      => Account::whereKey($byCode[$r[1]])->value('name'),
                    'type'              => $r[3],
                    'amount'            => $r[4],
                    'description'       => $r[6],
                ]
            );
        }
    }
}
