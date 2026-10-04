<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TourGuide;

class TourGuideSeeder extends Seeder
{
    public function run(): void
    {
        // [name, phone, languages, experience_years, status]
        $rows = [
            ['Sami Rahman',   '+8801711000001', 'English, Bengali, Arabic', 15, 'active'],
            ['Lima Akter',    '+8801711000002', 'English, Hindi',           23, 'active'],
            ['Nabila Haque',  '+8801711000003', 'English, Bengali',          8, 'on_tour'],
            ['Karim Uddin',   '+8801711000004', 'Arabic, Urdu',             31, 'inactive'],
            ['Tanvir Hasan',  '+8801711000005', 'English, Bengali, Hindi',  11, 'active'],
            ['Mitu Rahman',   '+8801711000006', 'English, Thai',             6, 'active'],
            ['Rafiq Islam',   '+8801711000007', 'English, Malay, Bengali',  19, 'on_tour'],
        ];

        foreach ($rows as $r) {
            TourGuide::updateOrCreate(
                ['name' => $r[0]],
                [
                    'phone'            => $r[1],
                    'languages'        => $r[2],
                    'experience_years' => $r[3],
                    'status'           => $r[4],
                ]
            );
        }
    }
}
