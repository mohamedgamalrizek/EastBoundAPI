<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\HotelRoom;

class HotelRoomSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = Hotel::all()->keyBy(fn ($h) => $h->city . '|' . $h->name);

        if ($hotels->isEmpty()) {
            return;
        }

        // [ hotelKey(city|name), room_type, capacity, rate_per_night, total_rooms, available_rooms ]
        $rows = [
            ['Makkah|Hilton Makkah',        'Deluxe King',     2, 17200.00, 40, 12],
            ['Makkah|Hilton Makkah',        'Twin Standard',   2, 14500.00, 50, 0],
            ['Dubai|Atlantis The Palm',     'Ocean Suite',     3, 62000.00, 30, 8],
            ['Dubai|Atlantis The Palm',     'Royal Bridge',    4, 95000.00, 6,  2],
            ['Makkah|Pullman Zamzam',       'Haram View',      2, 28500.00, 34, 5],
            ['Makkah|Pullman Zamzam',       'Standard Double', 2, 22000.00, 30, 0],
            ['Makkah|Swissotel Al Maqam',   'Premium King',    2, 21000.00, 45, 18],
            ['Madinah|Movenpick Madinah',   'Family Room',     4, 15800.00, 36, 10],
            ['Madinah|Movenpick Madinah',   'Superior Twin',   2, 13200.00, 36, 0],
            ['Dhaka|Le Meridien Dhaka',     'Executive Suite', 2, 12500.00, 55, 22],
            ['Sylhet|Grand Sylhet',         'Standard Room',   2, 6500.00,  40, 16],
            ["Cox's Bazar|Sea Pearl Cox",   'Sea View Deluxe', 3, 9800.00,  58, 25],
        ];

        foreach ($rows as $r) {
            $hotel = $hotels->get($r[0]);
            if (! $hotel) {
                continue;
            }

            HotelRoom::updateOrCreate(
                ['hotel_id' => $hotel->id, 'room_type' => $r[1]],
                [
                    'capacity'        => $r[2],
                    'rate_per_night'  => $r[3],
                    'total_rooms'     => $r[4],
                    'available_rooms' => $r[5],
                    'status'          => $r[5] > 0 ? 'available' : 'sold-out',
                ]
            );
        }
    }
}
