<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Insurance;

class InsuranceSeeder extends Seeder
{
    public function run(): void
    {
        // Each demo record is put on a customer's profile: without customer_id
        // the sale exists but cannot be seen from the customer it was made to.
        $customerIds = \App\Models\Customer::pluck('id')->all();

        $rows = [
            // provider, plan_name, type, coverage, premium, status
            ['Green Delta',      'TravelCare Basic',     'Single Trip', 500000,  1200,  'active'],
            ['Green Delta',      'TravelCare Gold',      'Single Trip', 2000000, 3500,  'active'],
            ['MetLife',          'Global Voyager',       'Multi Trip',  5000000, 12000, 'active'],
            ['Pragati Insurance','Umrah Shield',         'Single Trip', 1000000, 1800,  'active'],
            ['Sena Kalyan',      'Student Guard Annual', 'Multi Trip',  3000000, 8500,  'active'],
            ['City Insurance',   'Economy Trip Cover',   'Single Trip', 300000,  800,   'inactive'],
        ];

        foreach ($rows as $i => $r) {
            Insurance::updateOrCreate(
                ['provider' => $r[0], 'plan_name' => $r[1]],
                [
                    'customer_id' => $customerIds ? $customerIds[$i % count($customerIds)] : null,
                    'type'     => $r[2],
                    'coverage' => $r[3],
                    'premium'  => $r[4],
                    'status'   => $r[5],
                ]
            );
        }
    }
}
