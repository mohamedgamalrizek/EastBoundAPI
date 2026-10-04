<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slider;

/**
 * Home-page hero. The lowest `sort_order` active row drives the hero headline,
 * sub-headline, badge, background image and call-to-action button.
 */
class SliderSeeder extends Seeder
{
    public function run(): void
    {
        // Demo art comes from demo_image(); see the helper — it is removed before submission.
        $img = fn ($id) => demo_image($id, 'slide');

        $rows = [
            [
                'title'       => 'Discover the world, your way.',
                'subtitle'    => 'Tours, visas, flights, hotels and Hajj & Umrah — booked in minutes and managed end to end by real travel experts.',
                'badge'       => "Bangladesh's trusted travel partner",
                'cta_text'    => 'Browse packages',
                'cta_link'    => '/tour-packages',
                'image_label' => 'Hero — default',
                'image'       => $img('1469854523086-cc02fe5d8800'),
                'sort_order'  => 1,
                'status'      => 'active',
            ],
            [
                'title'       => 'Maldives, without the guesswork.',
                'subtitle'    => 'Overwater villas, seaplane transfers and full-board stays arranged as one package.',
                'badge'       => 'Island escapes',
                'cta_text'    => 'See Maldives tours',
                'cta_link'    => '/tour-packages?q=Maldives',
                'image_label' => 'Hero — Maldives',
                'image'       => $img('1514282401047-d79a71a590e8'),
                'sort_order'  => 2,
                'status'      => 'active',
            ],
            [
                'title'       => 'Hajj & Umrah, handled with care.',
                'subtitle'    => 'Government-approved packages, hotels close to the Haram and guided groups throughout.',
                'badge'       => 'Pilgrimage',
                'cta_text'    => 'View Umrah packages',
                'cta_link'    => '/umrah',
                'image_label' => 'Hero — Umrah',
                'image'       => $img('1591604129939-f1efa4d9f7fa'),
                'sort_order'  => 3,
                'status'      => 'active',
            ],
            [
                'title'       => 'Dubai, city of everything.',
                'subtitle'    => 'Desert safaris, skyline dinners and shopping — with a visa sorted before you fly.',
                'badge'       => 'City breaks',
                'cta_text'    => 'Plan a Dubai trip',
                'cta_link'    => '/tour-packages?q=Dubai',
                'image_label' => 'Hero — Dubai',
                'image'       => $img('1512453979798-5ea266f8880c'),
                'sort_order'  => 4,
                'status'      => 'inactive',
            ],
        ];

        foreach ($rows as $row) {
            Slider::updateOrCreate(['title' => $row['title']], $row);
        }

        // Earlier demo sliders carried no image or call-to-action, so one of
        // them could win the hero slot and render a blank background. Retire
        // them behind the real set rather than deleting an agency's rows.
        Slider::whereIn('title', [
            'Explore the World With Us',
            'Maldives Getaway',
            'Hajj & Umrah Packages',
            'Dubai City Tour',
            'European Summer Escapes',
            'Bali Island Retreat',
        ])->whereNull('image')->update(['status' => 'inactive', 'sort_order' => 90]);
    }
}
