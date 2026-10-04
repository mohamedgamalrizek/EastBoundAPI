<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobOpening;

/**
 * Vacancies rendered on the public /career page.
 */
class JobOpeningSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Travel Sales Consultant',    'Sales',      'Dhaka (On-site)', 'Full-time', 'Advise walk-in and phone customers on packages, prepare quotes and close bookings.',            1],
            ['Visa Processing Officer',    'Operations', 'Dhaka (On-site)', 'Full-time', 'Verify applicant documents, complete embassy forms and track application status end to end.', 2],
            ['Customer Support Executive', 'Support',    'Remote',          'Full-time', 'Handle support tickets and live chat, and coordinate with operations on live bookings.',       3],
            ['Digital Marketing Specialist','Marketing', 'Dhaka (Hybrid)',  'Full-time', 'Own paid social, SEO and the content calendar for the website and campaigns.',                  4],
            ['Hajj & Umrah Coordinator',   'Operations', 'Dhaka (On-site)', 'Seasonal',  'Coordinate pilgrim groups, hotel allocation and ground logistics during the season.',           5],
            ['Frontend Developer',         'Technology', 'Remote',          'Full-time', 'Build and maintain the public website and internal dashboards with Laravel and Blade.',         6],
        ];

        foreach ($rows as $r) {
            JobOpening::updateOrCreate(
                ['title' => $r[0]],
                [
                    'department'      => $r[1],
                    'location'        => $r[2],
                    'employment_type' => $r[3],
                    'description'     => $r[4],
                    'sort_order'      => $r[5],
                    'closing_date'    => null,
                    'status'          => 'active',
                ]
            );
        }
    }
}
