<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudentService;

class StudentServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Each demo record is put on a customer's profile: without customer_id
        // the sale exists but cannot be seen from the customer it was made to.
        $customerIds = \App\Models\Customer::pluck('id')->all();

        $rows = [
            // student_name, university, country, service_type, status
            ['Tahmid Hasan',    'University of Toronto',        'Canada',    'University Admission', 'Processing'],
            ['Nabila Rahman',   'Monash University',            'Australia', 'Offer Letter',         'Completed'],
            ['Sajid Chowdhury', 'University of Debrecen',       'Hungary',   'Student Visa',         'Processing'],
            ['Israt Jahan',     'Coventry University',          'UK',        'Student Visa',         'Pending'],
            ['Fahim Abrar',     'Istanbul Aydin University',    'Turkey',    'University Admission', 'Pending'],
            ['Maliha Khan',     'University of Saskatchewan',   'Canada',    'Accommodation',        'Completed'],
            ['Riyad Mahmud',    'Universiti Malaya',            'Malaysia',  'Offer Letter',         'Rejected'],
        ];

        foreach ($rows as $i => $r) {
            StudentService::updateOrCreate(
                ['student_name' => $r[0], 'service_type' => $r[3]],
                [
                    'customer_id' => $customerIds ? $customerIds[$i % count($customerIds)] : null,
                    'university' => $r[1],
                    'country'    => $r[2],
                    'status'     => $r[4],
                ]
            );
        }
    }
}
