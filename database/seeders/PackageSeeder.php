<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        // name => id. Every `category` below must be a real PackageCategory
        // name, so `category_id` and the denormalised `category` string always
        // agree — the public filters read the category, the admin panel edits it.
        $categoryIds = PackageCategory::pluck('id', 'name');

        $packages = [
            ['title' => "Cox's Bazar Beach Escape",      'destination' => "Cox's Bazar, Bangladesh", 'category' => 'Beach',      'price' => 18500,  'duration_days' => 3, 'duration_nights' => 2, 'image' => demo_image('1514282401047-d79a71a590e8', 'card')],
            ['title' => "Maldives Luxury Honeymoon",     'destination' => "Malé, Maldives",          'category' => 'Honeymoon',  'price' => 145000, 'duration_days' => 5, 'duration_nights' => 4, 'image' => demo_image('1506744038136-46273834b3fb', 'card')],
            ['title' => "Dubai City & Desert Safari",    'destination' => "Dubai, UAE",              'category' => 'City Tour',  'price' => 89000,  'duration_days' => 4, 'duration_nights' => 3, 'image' => demo_image('1512453979798-5ea266f8880c', 'card')],
            ['title' => "Bali Island Adventure",         'destination' => "Bali, Indonesia",         'category' => 'Adventure',  'price' => 112000, 'duration_days' => 6, 'duration_nights' => 5, 'image' => demo_image('1537996194471-e657df975ab4', 'card')],
            ['title' => "Kashmir Valley Tour",           'destination' => "Srinagar, India",         'category' => 'Family',     'price' => 64000,  'duration_days' => 5, 'duration_nights' => 4, 'image' => demo_image('1436491865332-7a61a109cc05', 'card')],
            ['title' => "Istanbul Heritage Trail",       'destination' => "Istanbul, Turkey",        'category' => 'City Tour',  'price' => 98000,  'duration_days' => 5, 'duration_nights' => 4, 'image' => demo_image('1488646953014-85cb44e25828', 'card')],
            ['title' => "Thailand Phuket & Krabi",       'destination' => "Phuket, Thailand",        'category' => 'Beach',      'price' => 76000,  'duration_days' => 5, 'duration_nights' => 4, 'image' => demo_image('1502602898657-3e91760cbb34', 'card')],
            ['title' => "Swiss Alps Explorer",           'destination' => "Interlaken, Switzerland", 'category' => 'Luxury',     'price' => 245000, 'duration_days' => 7, 'duration_nights' => 6, 'image' => demo_image('1469854523086-cc02fe5d8800', 'card')],
            ['title' => "Premium Umrah Package 2026",    'destination' => "Makkah & Madinah, KSA",   'category' => 'Pilgrimage', 'price' => 165000, 'duration_days' => 12,'duration_nights' => 11,'image' => demo_image('1591604129939-f1efa4d9f7fa', 'card')],
        ];

        // Rendered as the per-package "What's included / Not included" lists on
        // the public package page — one item per line.
        $inclusions = implode("\n", [
            'Hotel accommodation',
            'Return air tickets',
            'Airport transfers',
            'Daily breakfast',
            'Guided sightseeing',
            'Visa assistance',
        ]);

        $exclusions = implode("\n", [
            'Visa government fees',
            'Travel insurance',
            'Personal expenses and tips',
            'Anything not listed under inclusions',
        ]);

        foreach ($packages as $p) {
            $package = Package::updateOrCreate(
                ['title' => $p['title']],
                array_merge($p, [
                    'category_id' => $categoryIds[$p['category']] ?? null,
                    'status'      => 'active',
                    'description' => 'Enjoy a carefully curated experience with handpicked hotels, guided tours, airport transfers, and 24/7 on-trip support. Includes accommodation, selected meals, and sightseeing as per itinerary.',
                    'inclusions'  => $inclusions,
                    'exclusions'  => $exclusions,
                ])
            );

            $this->seedItinerary($package);
        }
    }

    /**
     * A day-by-day plan sized to the package's duration. Replaces the fixed
     * five-day list the public page used to invent for every package.
     */
    private function seedItinerary(Package $package): void
    {
        $days = max(1, (int) $package->duration_days);
        $city = str($package->destination)->before(',')->trim()->toString() ?: $package->destination;

        $plan = [
            ['Arrival & check-in',      "Land in {$city}, meet your representative and transfer to the hotel. Evening free to settle in."],
            ['City tour & sightseeing', "A guided tour of the highlights of {$city}, with entry tickets and lunch included."],
            ['Excursion day',           'A full-day excursion outside the city — the exact route is confirmed with you before departure.'],
            ['Leisure & shopping',      'A relaxed day. Optional activities and local shopping, with your guide on call.'],
            ['Cultural experience',     'Local cuisine, markets and a cultural show or heritage site, depending on the season.'],
            ['Free day',                'The day is yours. We can arrange add-on activities at cost if you decide on the spot.'],
            ['Departure',               'Breakfast, checkout and a private transfer to the airport in time for your flight.'],
        ];

        $package->itineraries()->delete();

        for ($d = 1; $d <= $days; $d++) {
            // First days follow the plan in order; the last day is always departure.
            [$title, $description] = $d === $days && $days > 1
                ? $plan[count($plan) - 1]
                : $plan[min($d - 1, count($plan) - 2)];

            $package->itineraries()->create([
                'day_number'  => $d,
                'title'       => $title,
                'description' => $description,
            ]);
        }
    }
}
