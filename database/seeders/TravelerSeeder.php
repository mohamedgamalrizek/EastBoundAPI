<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Traveler;

class TravelerSeeder extends Seeder
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
            ['Ayesha Rahman', 'Self',   'BD1234567', 'Bangladeshi', '1989-04-12', 'Active'],
            ['Kamal Rahman',  'Spouse', 'BD7654321', 'Bangladeshi', '1985-08-03', 'Active'],
            ['Sara Rahman',   'Child',  'BD1122334', 'Bangladeshi', '2015-11-20', 'Active'],
            ['Hasan Rahman',  'Child',  'BD9988776', 'Bangladeshi', '2018-02-09', 'Active'],
            ['Nasima Begum',  'Parent', 'BD5544332', 'Bangladeshi', '1960-06-15', 'Active'],
            ['Rahim Rahman',  'Parent', 'BD3322110', 'Bangladeshi', '1958-12-01', 'Inactive'],
        ];

        foreach ($rows as $i => $r) {
            Traveler::updateOrCreate(
                ['passport_no' => $r[2]],
                [
                    'customer_id' => $customerIds ? $customerIds[$i % 2 === 0 ? 0 : ($i % count($customerIds))] : null,
                    'name'        => $r[0],
                    'relation'    => $r[1],
                    'nationality' => $r[3],
                    'dob'         => $r[4],
                    'status'      => $r[5],
                ]
            );
        }
    }
}
