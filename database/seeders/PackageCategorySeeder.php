<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackageCategory;

class PackageCategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Beach',     'Sun, sand and seaside resort getaways.',         'active'],
            ['City Tour', 'Guided urban sightseeing and culture tours.',    'active'],
            ['Honeymoon', 'Romantic packages for newlywed couples.',        'active'],
            ['Adventure', 'Trekking, diving and adrenaline experiences.',   'active'],
            ['Family',    'Kid-friendly multi-day family vacations.',       'active'],
            ['Luxury',    'Premium five-star curated travel.',              'active'],
            ['Pilgrimage','Umrah, Hajj and spiritual journeys.',           'active'],
            ['Cruise',    'Ocean and river cruise holidays.',               'inactive'],
        ];

        foreach ($rows as $r) {
            PackageCategory::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($r[0])],
                [
                    'name'        => $r[0],
                    'description' => $r[1],
                    'status'      => $r[2],
                ]
            );
        }
    }
}
