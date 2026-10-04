<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;

/**
 * Reviews rendered on the home page carousel and the /testimonials page.
 * `avatar` is optional — the frontend falls back to a generated initials image.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        // Demo art comes from demo_image(); see the helper — it is removed before submission.
        $img = fn ($id) => demo_image($id, 'avatar');

        $rows = [
            ['Ayesha Rahman',  'Maldives Honeymoon', 'Dhaka',      'Our Maldives honeymoon was flawless — every transfer and hotel was perfect. Highly recommend!',      5, 'active',   '1494790108377-be9c29b29330'],
            ['Tanvir Hasan',   'Schengen Visa',      'Chattogram', 'Got my Schengen visa approved in 10 days. The team handled all the paperwork professionally.',      5, 'active',   '1500648767791-00dcc994a43e'],
            ['Nusrat Jahan',   'Family Umrah',       'Sylhet',     'Hotels were right next to the Haram. A truly blessed and well-organised experience.',               5, 'active',   '1438761681033-6461ffad8d80'],
            ['Rakib Ahmed',    'Dubai Flight',       'Khulna',     'Cheapest Dubai flight I could find, plus a free desert safari add-on. Will book again!',            4, 'active',   '1507003211169-0a1dd7228f2d'],
            ['Farhana Akter',  'Bali Tour',          'Dhaka',      'Beautiful itinerary and the guide was fantastic. Every detail was taken care of.',                 5, 'active',   '1544005313-94ddf0286df2'],
            ['Imran Khan',     "Cox's Bazar",        'Rajshahi',   'Smooth booking, great hotel and amazing support throughout the trip.',                             5, 'active',   '1506794778202-cad84cf45f1d'],
            ['Lima Akter',     'Switzerland Tour',   'Dhaka',      'Breathtaking itinerary — the Alps segment alone was worth it.',                                    5, 'active',   null],
            ['Sami Chowdhury', 'Bangkok Group Tour', 'Barishal',   'Quick responses and transparent pricing throughout. No hidden charges at all.',                    4, 'active',   null],
            ['Nabila Karim',   'Hajj',               'Cumilla',    'A truly spiritual journey made easy by their team.',                                               5, 'inactive', null],
        ];

        foreach ($rows as $r) {
            Testimonial::updateOrCreate(
                ['name' => $r[0]],
                [
                    'role'    => $r[1],
                    'city'    => $r[2],
                    'message' => $r[3],
                    'rating'  => $r[4],
                    'status'  => $r[5],
                    'avatar'  => $r[6] ? $img($r[6]) : null,
                ]
            );
        }
    }
}
