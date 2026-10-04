<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gallery;

/**
 * Photos rendered by the public /gallery page. `image` accepts an absolute URL
 * or a path under public/ — these seed rows use stock imagery.
 */
class GallerySeeder extends Seeder
{
    public function run(): void
    {
        // Demo art comes from demo_image(); see the helper — it is removed before submission.
        $img = fn ($id) => demo_image($id, 'wide');

        $rows = [
            ['Maldives Overwater Villa',  'Overwater villas, Maldives',   'Beach',    '1514282401047-d79a71a590e8', 1,  'active'],
            ['Dubai Skyline at Night',    'Downtown Dubai after dark',    'City',     '1512453979798-5ea266f8880c', 2,  'active'],
            ['Makkah Holy Kaaba',         'The Haram, Makkah',            'Hajj',     '1591604129939-f1efa4d9f7fa', 3,  'active'],
            ['Swiss Alps Retreat',        'Alpine morning, Switzerland',  'Mountain', '1506744038136-46273834b3fb', 4,  'active'],
            ['Bali Rice Terraces',        'Tegallalang, Bali',            'Nature',   '1537996194471-e657df975ab4', 5,  'active'],
            ['Santorini Sunset',          'Oia at golden hour',           'Beach',    '1507525428034-b723cf961d3e', 6,  'active'],
            ['Istanbul Bosphorus',        'Bosphorus, Istanbul',          'City',     '1524231757912-21f4fe3a7200', 7,  'active'],
            ["Cox's Bazar Sea Beach",     "Cox's Bazar shoreline",        'Beach',    '1530122037265-a5f1f91d3b99', 8,  'active'],
            ['Northern Lights',           'Aurora over Norway',           'Nature',   '1469854523086-cc02fe5d8800', 9,  'active'],
            ['Desert Safari',             'Dunes at dusk',                'Adventure','1488646953014-85cb44e25828', 10, 'active'],
            ['Luxury Resort Pool',        'Poolside, Sylhet',             'Hotel',    '1566073771259-6a8506099945', 11, 'active'],
            ['Tokyo Cherry Blossoms',     'Sakura season, Tokyo',         'City',     '1502602898657-3e91760cbb34', 12, 'inactive'],
        ];

        foreach ($rows as $r) {
            Gallery::updateOrCreate(
                ['title' => $r[0]],
                [
                    'image_label' => $r[1],
                    'category'    => $r[2],
                    'image'       => $img($r[3]),
                    'sort_order'  => $r[4],
                    'status'      => $r[5],
                ]
            );
        }
    }
}
