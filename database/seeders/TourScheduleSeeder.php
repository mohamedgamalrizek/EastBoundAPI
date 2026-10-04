<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\TourSchedule;

class TourScheduleSeeder extends Seeder
{
    /**
     * Departures for the packages in PackageSeeder.
     *
     * Dates are relative to the day you seed, never hard-coded — a demo seeded
     * with fixed 2026 dates turns into a wall of "Closed" a few months later.
     * `status` is only what an admin would have picked; the badge shown in the
     * panel is derived from seats and dates (TourSchedule::$effective_status),
     * so a sold-out or departed row reads Full / Closed on its own.
     */
    public function run(): void
    {
        // Titles that shipped with the old fixed-date demo data. Dropped so a
        // re-seed does not leave orphan departures for packages that never existed.
        TourSchedule::whereIn('package_title', [
            'Maldives Luxury Escape', 'Dubai City & Desert', 'Turkey Grand Tour',
            'Bali Honeymoon', 'Thailand Island Hopper', 'Saudi Umrah Package',
            'Singapore Family Fun', 'Nepal Himalaya Trek', 'Sri Lanka Highlights',
        ])->delete();

        $packages = Package::pluck('id', 'title');

        // [package title, days from today, nights, booked, seats, status]
        $rows = [
            ["Cox's Bazar Beach Escape",   8, 3, 12, 20, 'open'],
            ['Maldives Luxury Honeymoon', 15, 5, 18, 25, 'open'],
            ['Dubai City & Desert Safari', 22, 4, 22, 30, 'open'],
            ['Bali Island Adventure',      30, 6,  8, 20, 'open'],
            ['Istanbul Heritage Trail',    38, 5, 26, 28, 'open'],
            ['Thailand Phuket & Krabi',    45, 5, 14, 22, 'open'],
            ['Swiss Alps Explorer',        60, 7, 30, 30, 'open'],   // sold out -> reads Full
            ['Premium Umrah Package 2026', 75, 12, 40, 40, 'open'],  // sold out -> reads Full
            ['Kashmir Valley Tour',       -60, 5, 16, 16, 'closed'], // departed -> reads Closed
        ];

        foreach ($rows as [$title, $offset, $nights, $booked, $seats, $status]) {
            $start = now()->startOfDay()->addDays($offset);

            TourSchedule::updateOrCreate(
                ['package_title' => $title],
                [
                    'package_id' => $packages[$title] ?? null,
                    'start_date' => $start->toDateString(),
                    'end_date'   => $start->copy()->addDays($nights)->toDateString(),
                    'booked'     => $booked,
                    'seats'      => $seats,
                    'status'     => $status,
                ]
            );
        }
    }
}
