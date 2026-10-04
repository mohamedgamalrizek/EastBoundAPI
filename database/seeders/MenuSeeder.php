<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

/**
 * The public website's navigation. This is structural data, not demo content —
 * without it the site has no menus, so DatabaseSeeder runs it unconditionally.
 *
 * Header depth drives the rendering: a root with grandchildren becomes a mega
 * menu (each child is a column heading), a root with plain children becomes a
 * dropdown, and a childless root is a simple link.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTree($this->header(), Menu::POSITION_HEADER);
        $this->seedTree($this->footer(), Menu::POSITION_FOOTER);
        $this->seedTree($this->footerLegal(), Menu::POSITION_FOOTER_LEGAL);

        $this->retireLegacyItems();
    }

    /** Recursively upsert a tree, keyed on (position, parent, title). */
    private function seedTree(array $items, string $position, ?int $parentId = null): void
    {
        foreach ($items as $i => $item) {
            $menu = Menu::updateOrCreate(
                ['position' => $position, 'parent_id' => $parentId, 'title' => $item['title']],
                [
                    'url'        => $item['url']    ?? null,
                    'icon'       => $item['icon']   ?? null,
                    'target'     => $item['target'] ?? null,
                    'sort_order' => $i + 1,
                    'status'     => $item['status'] ?? 'active',
                ]
            );

            if (! empty($item['children'])) {
                $this->seedTree($item['children'], $position, $menu->id);
            }
        }
    }

    /**
     * The first version of this seeder created flat rows pointing at paths that
     * never existed (/packages, /destinations) or that have since moved
     * (/about → /about-us). Left alone they would render as broken links.
     */
    private function retireLegacyItems(): void
    {
        $stale = [
            ['Packages',         '/packages'],
            ['Destinations',     '/destinations'],
            ['About Us',         '/about'],
            ['Contact',          '/contact'],
            ['Privacy Policy',   '/privacy'],
            ['Terms of Service', '/terms'],
        ];

        foreach ($stale as [$title, $url]) {
            Menu::whereNull('parent_id')->where('title', $title)->where('url', $url)->delete();
        }
    }

    private function header(): array
    {
        return [
            ['title' => 'Home', 'url' => '/'],

            // Mega menu — children are column headings, grandchildren are links.
            ['title' => 'Services', 'children' => [
                ['title' => 'Plan a Trip', 'children' => [
                    ['title' => 'Tour Packages',  'url' => '/tour-packages',     'icon' => 'fa-umbrella-beach'],
                    ['title' => 'Flight Booking', 'url' => '/flight-booking',    'icon' => 'fa-plane'],
                    ['title' => 'Hotel Booking',  'url' => '/hotel-booking',     'icon' => 'fa-hotel'],
                    ['title' => 'Transport',      'url' => '/transport-booking', 'icon' => 'fa-van-shuttle'],
                ]],
                ['title' => 'Visa & Faith', 'children' => [
                    ['title' => 'Visa Services', 'url' => '/visa-services', 'icon' => 'fa-passport'],
                    ['title' => 'Hajj',          'url' => '/hajj',          'icon' => 'fa-kaaba'],
                    ['title' => 'Umrah',         'url' => '/umrah',         'icon' => 'fa-mosque'],
                    ['title' => 'Track Visa',    'url' => '/track-visa',    'icon' => 'fa-location-crosshairs'],
                ]],
                ['title' => 'Company', 'children' => [
                    ['title' => 'About Us',        'url' => '/about-us',        'icon' => 'fa-circle-info'],
                    ['title' => 'Career',          'url' => '/career',          'icon' => 'fa-briefcase'],
                    ['title' => 'Become an Agent', 'url' => '/become-an-agent', 'icon' => 'fa-handshake'],
                    ['title' => 'Support Center',  'url' => '/support-center',  'icon' => 'fa-headset'],
                ]],
            ]],

            // Plain dropdown — children are links, no grandchildren.
            ['title' => 'Pages', 'children' => [
                ['title' => 'Gallery',            'url' => '/gallery'],
                ['title' => 'FAQ',                'url' => '/faq'],
                ['title' => 'Testimonials',       'url' => '/testimonials'],
                ['title' => 'Track Booking',      'url' => '/track-booking'],
                ['title' => 'Privacy Policy',     'url' => '/privacy-policy'],
                ['title' => 'Terms & Conditions', 'url' => '/terms-conditions'],
            ]],

            ['title' => 'Blog',    'url' => '/blog'],
            ['title' => 'Contact', 'url' => '/contact-us'],
        ];
    }

    private function footer(): array
    {
        return [
            ['title' => 'Services', 'children' => [
                ['title' => 'Tour Packages',  'url' => '/tour-packages'],
                ['title' => 'Visa Services',  'url' => '/visa-services'],
                ['title' => 'Flight Booking', 'url' => '/flight-booking'],
                ['title' => 'Hotel Booking',  'url' => '/hotel-booking'],
                ['title' => 'Hajj & Umrah',   'url' => '/hajj'],
            ]],
            ['title' => 'Company', 'children' => [
                ['title' => 'About Us',        'url' => '/about-us'],
                ['title' => 'Career',          'url' => '/career'],
                ['title' => 'Become an Agent', 'url' => '/become-an-agent'],
                ['title' => 'Blog',            'url' => '/blog'],
                ['title' => 'Contact',         'url' => '/contact-us'],
            ]],
        ];
    }

    private function footerLegal(): array
    {
        return [
            ['title' => 'Privacy',      'url' => '/privacy-policy'],
            ['title' => 'Terms',        'url' => '/terms-conditions'],
            ['title' => 'Refund',       'url' => '/refund-policy'],
            ['title' => 'Cancellation', 'url' => '/cancellation-policy'],
        ];
    }
}
