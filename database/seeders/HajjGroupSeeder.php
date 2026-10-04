<?php

namespace Database\Seeders;

use App\Models\HajjFlight;
use App\Models\HajjGroup;
use Illuminate\Database\Seeder;

/**
 * The groups and flights a hajj batch is run with.
 *
 * These are catalogues, not sample content: the Groups and Flight Allocation
 * screens pick from them, so without at least one of each those screens have
 * empty dropdowns and nothing can be allocated. The pilgrim seeder places
 * people into the groups named here.
 */
class HajjGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['name' => 'Group A', 'leader' => 'Mawlana Abdul Hakim'],
            ['name' => 'Group B', 'leader' => 'Mawlana Nurul Islam'],
            ['name' => 'Group C', 'leader' => 'Hafez Shafiqul Alam'],
        ];

        foreach ($groups as $group) {
            HajjGroup::updateOrCreate(['name' => $group['name']], $group);
        }

        // Two aircraft the batch flies on. 30 rows × ABCDEF = 180 seats each,
        // which is what the seat dropdown offers.
        $flights = [
            [
                'flight_no'      => 'BG-1011',
                'airline'        => 'Biman Bangladesh Airlines',
                'departure_date' => now()->addMonths(2)->toDateString(),
                'return_date'    => now()->addMonths(3)->toDateString(),
                'seat_rows'      => 30,
                'seat_letters'   => 'ABCDEF',
            ],
            [
                'flight_no'      => 'SV-3801',
                'airline'        => 'Saudia',
                'departure_date' => now()->addMonths(2)->addDays(3)->toDateString(),
                'return_date'    => now()->addMonths(3)->addDays(3)->toDateString(),
                'seat_rows'      => 28,
                'seat_letters'   => 'ABCDEF',
            ],
        ];

        foreach ($flights as $flight) {
            HajjFlight::updateOrCreate(['flight_no' => $flight['flight_no']], $flight);
        }
    }
}
