<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\WalletTransaction;
use App\Services\Accounting\CustomerWalletService;

/**
 * Customer wallet demo data.
 *
 * Only top-ups and adjustments are seeded — the kind of line a customer makes
 * on purpose. Anything spent from the wallet comes from a receipt, and
 * anything refunded into it comes from a refund, so seeding those here would
 * invent money that no document backs.
 *
 * Running balances are recomputed rather than written, so the statement always
 * agrees with its own rows.
 */
class WalletTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Legacy rows: hand-written balances with no document behind them.
        WalletTransaction::whereNull('source_type')
            ->where('reference', 'like', 'WTX-%')
            ->delete();

        $customers = Customer::orderByRaw("FIELD(email, 'customer@bugbuild.com') DESC")
            ->orderBy('id')->take(4)->get();

        // reference, type, amount, description, method, date
        $rows = [
            ['TOPUP-1001', 'credit', 15000, 'Wallet top-up',        'bKash', '2026-06-01'],
            ['TOPUP-1002', 'credit', 5000,  'Referral bonus',       'Cash',  '2026-06-04'],
            ['TOPUP-1003', 'credit', 8000,  'Wallet top-up',        'Bank',  '2026-06-08'],
            ['ADJ-1004',   'debit',  1500,  'Service charge',       'Cash',  '2026-06-10'],
            ['TOPUP-1005', 'credit', 12000, 'Wallet top-up',        'Card',  '2026-06-12'],
            ['TOPUP-1006', 'credit', 3000,  'Goodwill credit',      'Cash',  '2026-06-15'],
        ];

        if ($customers->isEmpty()) {
            return;
        }

        foreach ($rows as $i => $r) {
            $customer = $customers[$i % $customers->count()];

            WalletTransaction::updateOrCreate(
                ['reference' => $r[0]],
                [
                    'customer_id'   => $customer->id,
                    'type'          => $r[1],
                    'amount'        => $r[2],
                    'description'   => $r[3],
                    'method'        => $r[4],
                    'txn_date'      => $r[5],
                    'balance_after' => 0, // recomputed below
                ]
            );
        }

        app(CustomerWalletService::class)->rebuildAll();
    }
}
