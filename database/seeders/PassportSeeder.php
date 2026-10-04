<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Passport;

class PassportSeeder extends Seeder
{
    public function run(): void
    {
        // Demo portal customer first: the customer portal only ever shows a
        // customer their own travellers, so a random spread left the account
        // people log in with holding none.
        $customerIds = \App\Models\Customer::orderByRaw("FIELD(email, 'customer@bugbuild.com') DESC")
            ->orderBy('id')
            ->pluck('id')->all();

        $rows = [
            ['AYESHA RAHMAN', 'BD1234567', 'Bangladeshi', '2022-01-15', '2032-01-14', 'Valid'],
            ['KAMAL RAHMAN',  'BD7654321', 'Bangladeshi', '2021-03-10', '2031-03-09', 'Valid'],
            ['SARA RAHMAN',   'BD1122334', 'Bangladeshi', '2023-05-20', '2028-05-19', 'Valid'],
            ['HASAN RAHMAN',  'BD9988776', 'Bangladeshi', '2020-07-01', '2026-06-30', 'Expiring'],
            ['NASIMA BEGUM',  'BD5544332', 'Bangladeshi', '2016-09-12', '2026-09-11', 'Expiring'],
            ['RAHIM RAHMAN',  'BD3322110', 'Bangladeshi', '2014-11-05', '2024-11-04', 'Expired'],
        ];

        foreach ($rows as $i => $r) {
            Passport::updateOrCreate(
                ['passport_no' => $r[1]],
                [
                    'customer_id' => $customerIds ? $customerIds[$i % 2 === 0 ? 0 : ($i % count($customerIds))] : null,
                    'holder_name' => $r[0],
                    'nationality' => $r[2],
                    'issue_date'  => $r[3],
                    'expiry_date' => $r[4],
                    'status'      => $r[5],
                ]
            );
        }
    }
}
