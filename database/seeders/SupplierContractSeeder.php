<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\SupplierTransaction;
use Illuminate\Database\Seeder;

/**
 * Demo agreements and account history so the Contracts and Ledger screens have
 * something real to show. Balances are rebuilt from the entries, never typed.
 */
class SupplierContractSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::orderBy('id')->get();

        if ($suppliers->isEmpty()) {
            return;
        }

        // [rate type, value, commission %, credit days, months of cover, status]
        $templates = [
            ['Net rate',   850000, null, 30, 12, 'Active'],
            ['Commission', 0,      8.5,  15, 12, 'Active'],
            ['Fixed',      420000, null, 45, 6,  'Active'],
            ['Credit',     250000, null, 60, 12, 'Draft'],
        ];

        foreach ($suppliers as $i => $supplier) {
            [$rateType, $value, $commission, $creditDays, $months, $status] = $templates[$i % count($templates)];

            $start = now()->startOfYear()->addDays($i * 9);

            $contract = SupplierContract::updateOrCreate(
                ['contract_no' => 'SUP-' . now()->year . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'supplier_id'     => $supplier->id,
                    'title'           => $supplier->type . ' supply agreement — ' . $supplier->name,
                    'rate_type'       => $rateType,
                    'value'           => $value,
                    'commission_rate' => $commission,
                    'credit_days'     => $creditDays,
                    'start_date'      => $start->toDateString(),
                    'end_date'        => $start->copy()->addMonths($months)->toDateString(),
                    'terms'           => 'Rates held for the contract period. Cancellations outside the agreed window are billable.',
                    'status'          => $status,
                ]
            );

            // Only Active contracts carry trading history.
            if ($status !== 'Active') {
                continue;
            }

            SupplierTransaction::where('supplier_id', $supplier->id)->delete();

            $bills = [
                [$start->copy()->addMonths(1), 'Bill',    'INV-' . (1000 + $i * 3 + 1), 'Monthly consolidated invoice', 120000 + $i * 7500],
                [$start->copy()->addMonths(1)->addDays(20), 'Payment', 'PMT-' . (500 + $i * 2 + 1), 'Bank transfer',    100000 + $i * 5000],
                [$start->copy()->addMonths(2), 'Bill',    'INV-' . (1000 + $i * 3 + 2), 'Monthly consolidated invoice',  95000 + $i * 6200],
                [$start->copy()->addMonths(2)->addDays(18), 'Payment', 'PMT-' . (500 + $i * 2 + 2), 'Bank transfer',     80000 + $i * 4000],
                [$start->copy()->addMonths(3), 'Credit Note', 'CN-' . (200 + $i), 'Rate adjustment for group booking', 8000 + $i * 500],
            ];

            $running = 0.0;

            foreach ($bills as [$date, $type, $reference, $description, $amount]) {
                $isCredit = in_array($type, SupplierTransaction::CREDIT_TYPES, true);
                $running += $isCredit ? $amount : -$amount;

                SupplierTransaction::create([
                    'supplier_id'          => $supplier->id,
                    'supplier_contract_id' => $contract->id,
                    'txn_date'             => $date->toDateString(),
                    'type'                 => $type,
                    'reference'            => $reference,
                    'description'          => $description,
                    'credit'               => $isCredit ? $amount : 0,
                    'debit'                => $isCredit ? 0 : $amount,
                    'balance_after'        => $running,
                ]);
            }

            $supplier->recalculateBalance();
        }
    }
}
