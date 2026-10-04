<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventTour;

class EventTourSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // title, type, location, event_date offset (days), seats, status
            ['Dubai Expo Business Delegation',    'Exhibition',        'Dubai, UAE',          40,  25, 'Open'],
            ['Singapore FinTech Festival Tour',   'Conference',        'Singapore',           75,  20, 'Open'],
            ['Canton Fair Sourcing Trip',         'Trade Fair',        'Guangzhou, China',    55,  30, 'Open'],
            ['Leadership Retreat — Bali',         'Corporate Retreat', 'Bali, Indonesia',     90,  15, 'Full'],
            ['ITB Berlin Travel Trade Show',      'Trade Fair',        'Berlin, Germany',     120, 10, 'Open'],
            ['HR Summit Kuala Lumpur',            'Conference',        'Kuala Lumpur',        -20, 18, 'Closed'],
        ];

        foreach ($rows as $r) {
            EventTour::updateOrCreate(
                ['title' => $r[0]],
                [
                    'type'       => $r[1],
                    'location'   => $r[2],
                    'event_date' => now()->addDays($r[3])->toDateString(),
                    'seats'      => $r[4],
                    'status'     => $r[5],
                ]
            );
        }
    }
}
