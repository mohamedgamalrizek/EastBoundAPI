<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FlightRoute;

/**
 * Published fare deals rendered on /flight-booking, and the source of the
 * "trusted airlines" strip on the home page.
 */
class FlightRouteSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Dhaka', 'DAC', 'Dubai',         'DXB', 'Emirates',          38500, 1, true],
            ['Dhaka', 'DAC', 'Jeddah',        'JED', 'Saudia',            52000, 2, true],
            ['Dhaka', 'DAC', 'Kuala Lumpur',  'KUL', 'Malaysia Airlines', 29900, 3, true],
            ['Dhaka', 'DAC', 'Singapore',     'SIN', 'Singapore Airlines',41200, 4, false],
            ['Dhaka', 'DAC', 'Istanbul',      'IST', 'Turkish Airlines',  62000, 5, false],
            ['Dhaka', 'DAC', 'London',        'LHR', 'Biman Bangladesh',  89000, 6, false],
            ['Dhaka', 'DAC', 'Doha',          'DOH', 'Qatar Airways',     44500, 7, false],
            ['Dhaka', 'DAC', 'Abu Dhabi',     'AUH', 'Etihad Airways',    39900, 8, false],
        ];

        foreach ($rows as $r) {
            FlightRoute::updateOrCreate(
                ['origin' => $r[0], 'destination' => $r[2], 'airline' => $r[4]],
                [
                    'origin_code'      => $r[1],
                    'destination_code' => $r[3],
                    'fare'             => $r[5],
                    'trip_type'        => 'Round-trip',
                    'sort_order'       => $r[6],
                    'is_featured'      => $r[7],
                    'status'           => 'active',
                ]
            );
        }
    }
}
