<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CorporateTravel;

class CorporateTravelSeeder extends Seeder
{
    public function run(): void
    {
        // Each demo record is put on a customer's profile: without customer_id
        // the sale exists but cannot be seen from the customer it was made to.
        $customerIds = \App\Models\Customer::pluck('id')->all();

        $rows = [
            // company_name, contact_person, service_type, employees, budget, status
            ['BRAC Bank PLC',          'Md. Asif Iqbal (Admin)',   'Corporate Ticketing', 45, 3500000, 'active'],
            ['Square Pharmaceuticals', 'Sharmeen Sultana (HR)',    'Employee Travel',     30, 2200000, 'active'],
            ['Grameenphone Ltd',       'Tanjil Hoque (Procure)',   'Business Tour',       18, 1800000, 'active'],
            ['Akij Group',             'Rafiul Karim (Admin)',     'Hotel Reservation',   25, 1200000, 'active'],
            ['Pathao Ltd',             'Nafisa Anjum (People)',    'Business Tour',       60, 2800000, 'active'],
            ['Walton Hi-Tech',         'Mahbub Alam (Admin)',      'Corporate Ticketing', 12, 900000,  'inactive'],
        ];

        foreach ($rows as $i => $r) {
            CorporateTravel::updateOrCreate(
                ['company_name' => $r[0]],
                [
                    'customer_id' => $customerIds ? $customerIds[$i % count($customerIds)] : null,
                    'contact_person' => $r[1],
                    'service_type'   => $r[2],
                    'employees'      => $r[3],
                    'budget'         => $r[4],
                    'status'         => $r[5],
                ]
            );
        }
    }
}
