<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransportService;

/**
 * Transport products rendered on /transport-booking. `booking_type` must match
 * a key from FrontendController::bookingTypes() so "Book now" resolves.
 */
class TransportServiceSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Airport Transfer',    'fa-plane-arrival', 'Meet-and-greet pickup and drop in a private car.',        1200,  '',     'airport-pickup', 1, 'Airport'],
            ['Car Rental',          'fa-car-side',      'Sedans, SUVs and microbuses with or without a driver.',   3500,  '/day', 'car-rental',     2, 'Car'],
            ['Intercity Transport', 'fa-van-shuttle',   'Comfortable AC vehicles for cross-country trips.',        6000,  '',     'car-rental',     3, 'Car'],
            ['Group Coach',         'fa-bus',           '40-seater coaches for tours and corporate travel.',       18000, '',     'car-rental',     4, 'Bus'],
            ['Airport Drop',        'fa-plane-departure','Comfortable, on-time drop-off to the airport.',          1200,  '',     'airport-drop',   5, 'Airport'],
        ];

        foreach ($rows as $r) {
            TransportService::updateOrCreate(
                ['title' => $r[0]],
                [
                    'icon'         => $r[1],
                    'description'  => $r[2],
                    'price_from'   => $r[3],
                    'price_unit'   => $r[4],
                    'booking_type' => $r[5],
                    'sort_order'   => $r[6],
                    'vehicle_type' => $r[7] ?? null,
                    'status'       => 'active',
                ]
            );
        }
    }
}
