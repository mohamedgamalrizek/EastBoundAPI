<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobApplication;
use App\Models\JobOpening;

class JobApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $openingIds = JobOpening::orderBy('id')->pluck('id')->all();

        if (empty($openingIds)) {
            // JobOpeningSeeder must run first; nothing to attach applications to.
            return;
        }

        $rows = [
            // name, email, phone, linkedin_url, cover_letter, status
            ['Sadia Afrin',     'sadia.afrin@gmail.com',   '+880 1711 880001', 'https://linkedin.com/in/sadia-afrin',   'I have 3 years of experience in ticketing and GDS (Sabre) at a Banani-based agency.',        'shortlisted'],
            ['Rakibul Hasan',   'rakibul.h@gmail.com',     '+880 1812 880002', 'https://linkedin.com/in/rakibul-hasan', 'Fresh graduate in Tourism & Hospitality from Dhaka University, eager to start in sales.',    'new'],
            ['Mim Chowdhury',   'mim.chowdhury@gmail.com', '+880 1913 880003', null,                                    'Worked 2 years as a visa processing officer handling Schengen and UK files.',               'reviewed'],
            ['Asif Mahmud',     'asif.mahmud@yahoo.com',   '+880 1614 880004', 'https://linkedin.com/in/asif-mahmud',   'Digital marketer with hands-on Meta and Google Ads experience in the travel niche.',        'new'],
            ['Priya Das',       'priya.das@gmail.com',     '+880 1515 880005', null,                                    'Customer support executive, fluent in English and Hindi, comfortable with night shifts.',   'hired'],
            ['Shakil Ahmed',    'shakil.ahmed@gmail.com',  '+880 1316 880006', null,                                    'Applying again after last cycle; completed IATA foundation course in the meantime.',        'rejected'],
        ];

        foreach ($rows as $i => $r) {
            JobApplication::updateOrCreate(
                ['email' => $r[1]],
                [
                    // Spread applications across the seeded openings round-robin.
                    'job_opening_id' => $openingIds[$i % count($openingIds)],
                    'name'           => $r[0],
                    'phone'          => $r[2],
                    'linkedin_url'   => $r[3],
                    'cover_letter'   => $r[4],
                    'status'         => $r[5],
                ]
            );
        }
    }
}
