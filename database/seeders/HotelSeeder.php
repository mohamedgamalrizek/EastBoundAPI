<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        // Demo art comes from demo_image(); see the helper — it is removed before submission.
        $img = fn ($id) => demo_image($id, 'card');

        // [ name, city, country, star, rooms, price/night, status, photo id, blurb, featured ]
        $rows = [
            ['Hilton Makkah',      'Makkah',       'KSA', 5, 120, 17200.00, 'active',   '1566073771259-6a8506099945', 'Steps from the Haram with panoramic Kaaba views.',            true],
            ['Atlantis The Palm',  'Dubai',        'UAE', 5, 86,  62000.00, 'active',   '1582719478250-c89cae4dc85b', 'Iconic Palm Jumeirah resort with a private beach and aquarium.', true],
            ['Pullman Zamzam',     'Makkah',       'KSA', 5, 64,  28500.00, 'active',   '1591604129939-f1efa4d9f7fa', 'Direct Haram access and full-board dining for pilgrims.',      false],
            ['Grand Sylhet',       'Sylhet',       'BD',  4, 40,  6500.00,  'inactive', '1571003123894-1f0594d2b5d9', 'Tea-garden retreat on the edge of the city.',                  false],
            ['Swissotel Al Maqam', 'Makkah',       'KSA', 5, 95,  21000.00, 'active',   '1520250497591-112f2f40a3f4', 'Part of the Abraj Al Bait complex, minutes from the Haram.',   false],
            ['Movenpick Madinah',  'Madinah',      'KSA', 5, 72,  15800.00, 'active',   '1551882547-ff40c63fe5fa',    'Facing the Prophet\'s Mosque, with family-sized rooms.',       false],
            ['Le Meridien Dhaka',  'Dhaka',        'BD',  5, 110, 12500.00, 'active',   '1564501049412-61c2a3083791', 'Business-class comfort close to the airport.',                 true],
            ['Sea Pearl Cox',      "Cox's Bazar",  'BD',  5, 58,  9800.00,  'active',   '1571003123894-1f0594d2b5d9', 'Beachfront resort with the longest sea view in the country.',  true],
        ];

        foreach ($rows as $r) {
            Hotel::updateOrCreate(
                ['name' => $r[0], 'city' => $r[1]],
                [
                    'country'         => $r[2],
                    'category'        => $r[3],
                    'rooms_count'     => $r[4],
                    'price_per_night' => $r[5],
                    'status'          => $r[6],
                    'image'           => $img($r[7]),
                    'description'     => $r[8],
                    'is_featured'     => $r[9],
                ]
            );
        }
    }
}
