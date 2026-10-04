<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payslip;

class PayslipSeeder extends Seeder
{
    public function run(): void
    {
        // Rows are spread over the staff users, always starting with the demo
        // Staff login — otherwise the staff portal (which shows only your own
        // rows) opens empty for the account people actually sign in with.
        $staffIds = \App\Models\User::orderByRaw("FIELD(email, 'staff@bugbuild.com') DESC")
            ->orderBy('id')
            ->pluck('id')->all();

        $rows = [
            // staff_name,  month,     basic,    allowances, deductions, net_pay,  status
            ['Sami Rahman', '2026-06', 45000.00, 12000.00, 3000.00, 54000.00, 'Pending'],
            ['Sami Rahman', '2026-05', 45000.00, 12000.00, 3000.00, 54000.00, 'Paid'],
            ['Sami Rahman', '2026-04', 45000.00, 12000.00, 3000.00, 54000.00, 'Paid'],
            ['Sami Rahman', '2026-03', 45000.00, 12000.00, 2500.00, 54500.00, 'Paid'],
            ['Sami Rahman', '2026-02', 45000.00, 10000.00, 3000.00, 52000.00, 'Paid'],
            ['Sami Rahman', '2026-01', 45000.00, 10000.00, 3000.00, 52000.00, 'Paid'],
            ['Sami Rahman', '2025-12', 42000.00, 10000.00, 2800.00, 49200.00, 'Paid'],
            ['Sami Rahman', '2025-11', 42000.00, 10000.00, 2800.00, 49200.00, 'Paid'],
            ['Sami Rahman', '2025-10', 42000.00,  9000.00, 2500.00, 48500.00, 'Paid'],
        ];

        foreach ($rows as $i => $r) {
            Payslip::updateOrCreate(
                ['staff_name' => $r[0], 'month' => $r[1]],
                [
                    // Every other payslip belongs to the demo Staff user.
                    'user_id'    => $staffIds ? $staffIds[$i % 2 === 0 ? 0 : ($i % count($staffIds))] : null,
                    'basic'      => $r[2],
                    'allowances' => $r[3],
                    'deductions' => $r[4],
                    'net_pay'    => $r[5],
                    'status'     => $r[6],
                ]
            );
        }
    }
}
