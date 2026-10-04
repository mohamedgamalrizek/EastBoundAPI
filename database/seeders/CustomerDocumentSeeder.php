<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerDocument;
use Illuminate\Support\Arr;

class CustomerDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $customerIds = \App\Models\Customer::pluck('id')->all();

        $rows = [
            ['Passport scan',   'PDF', 'passport-scan.pdf',   '2026-05-10', 'Verified'],
            ['NID copy',        'JPG', 'nid-copy.jpg',        '2026-05-10', 'Verified'],
            ['Bank statement',  'PDF', 'bank-statement.pdf',  '2026-06-01', 'Pending'],
            ['Photo (35x45)',   'JPG', 'photo-35x45.jpg',     '2026-05-12', 'Verified'],
            ['Visa form',       'PDF', 'visa-form.pdf',       '2026-06-03', 'Pending'],
            ['Hotel booking',   'PDF', 'hotel-booking.pdf',   '2026-06-05', 'Rejected'],
        ];

        foreach ($rows as $r) {
            CustomerDocument::updateOrCreate(
                ['title' => $r[0]],
                [
                    'customer_id' => $customerIds ? Arr::random($customerIds) : null,
                    'type'        => $r[1],
                    'file_label'  => $r[2],
                    'uploaded_on' => $r[3],
                    'status'      => $r[4],
                ]
            );
        }
    }
}
