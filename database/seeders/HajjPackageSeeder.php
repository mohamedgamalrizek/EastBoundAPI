<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HajjPackage;

class HajjPackageSeeder extends Seeder
{
    public function run(): void
    {
        // package_no, title, type, duration_days, price, seats, status
        $rows = [
            ['UMR-ECO', 'Umrah Economy',  'Umrah', 14, 185000.00, 42, 'active'],
            ['UMR-PRM', 'Umrah Premium',  'Umrah', 14, 320000.00, 28, 'active'],
            ['UMR-FAM', 'Umrah Family',   'Umrah', 10, 410000.00, 36, 'active'],
            ['UMR-RAM', 'Umrah Ramadan',  'Umrah', 21, 525000.00, 20, 'active'],
            ['HAJ-STD', 'Hajj Standard',  'Hajj',  40, 650000.00, 60, 'active'],
            ['HAJ-VIP', 'Hajj VIP',       'Hajj',  40, 980000.00, 15, 'closed'],
            ['HAJ-SHF', 'Hajj Shifting',  'Hajj',  38, 720000.00, 45, 'active'],
            ['HAJ-NSF', 'Hajj Non-Shifting', 'Hajj', 42, 890000.00, 30, 'active'],
        ];

        foreach ($rows as $r) {
            HajjPackage::updateOrCreate(
                ['package_no' => $r[0]],
                [
                    'title'         => $r[1],
                    'type'          => $r[2],
                    'duration_days' => $r[3],
                    'price'         => $r[4],
                    'seats'         => $r[5],
                    'status'        => $r[6],
                ]
            );
        }
    }
}
