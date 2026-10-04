<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalTour;

class MedicalTourSeeder extends Seeder
{
    public function run(): void
    {
        // Each demo record is put on a customer's profile: without customer_id
        // the sale exists but cannot be seen from the customer it was made to.
        $customerIds = \App\Models\Customer::pluck('id')->all();

        $rows = [
            // patient_name, destination, hospital, treatment, cost, status
            ['Abdur Rouf',     'Chennai, India',    'Apollo Hospitals',            'Cardiac bypass surgery',      850000, 'Confirmed'],
            ['Salma Khatun',   'Bangkok, Thailand', 'Bumrungrad International',    'Orthopedic knee replacement', 950000, 'Inquiry'],
            ['Habibur Rahman', 'Vellore, India',    'CMC Vellore',                 'Neurology consultation',      220000, 'Completed'],
            ['Rehana Parvin',  'Singapore',         'Mount Elizabeth Hospital',    'Oncology second opinion',     450000, 'Inquiry'],
            ['Golam Mostofa',  'Kolkata, India',    'Fortis Hospital Anandapur',   'Kidney stone laser surgery',  180000, 'Completed'],
            ['Shirin Akter',   'Chennai, India',    'Sankara Nethralaya',          'Eye — retina treatment',      160000, 'Cancelled'],
        ];

        foreach ($rows as $i => $r) {
            MedicalTour::updateOrCreate(
                ['patient_name' => $r[0], 'treatment' => $r[3]],
                [
                    'customer_id' => $customerIds ? $customerIds[$i % count($customerIds)] : null,
                    'destination' => $r[1],
                    'hospital'    => $r[2],
                    'cost'        => $r[4],
                    'status'      => $r[5],
                ]
            );
        }
    }
}
