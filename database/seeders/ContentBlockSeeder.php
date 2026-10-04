<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

/**
 * The icon cards across the public site, migrated out of the blade templates.
 * Structural, not demo data — without it those sections render empty.
 */
class ContentBlockSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->blocks() as $section => $items) {
            foreach ($items as $i => $item) {
                ContentBlock::updateOrCreate(
                    ['section' => $section, 'title' => $item['title']],
                    [
                        'icon'       => $item['icon'] ?? null,
                        'body'       => $item['body'] ?? null,
                        'url'        => $item['url']  ?? null,
                        // Only the demo carries artwork. This seeder is
                        // structural and runs on real installs too, where the
                        // agency uploads its own picture instead.
                        'image'      => config('app.demo') ? ($item['image'] ?? null) : null,
                        'sort_order' => $i + 1,
                        'status'     => 'active',
                    ]
                );
            }
        }
    }

    private function blocks(): array
    {
        return [
            'home_services' => [
                ['icon' => 'fa-passport',        'title' => 'Visa Services',  'body' => 'Tourist, business & student visas handled end to end.',            'url' => 'front.visa'],
                ['icon' => 'fa-plane',           'title' => 'Flight Booking', 'body' => 'Best fares with instant e-ticketing & 24/7 reissue.',              'url' => 'front.flights'],
                ['icon' => 'fa-hotel',           'title' => 'Hotel Booking',  'body' => 'From budget stays to 5-star resorts at guaranteed best rates.',    'url' => 'front.hotels'],
                ['icon' => 'fa-kaaba',           'title' => 'Hajj & Umrah',   'body' => 'Govt-approved packages, premium hotels near Haram, guided groups.', 'url' => 'front.hajj'],
                ['icon' => 'fa-umbrella-beach',  'title' => 'Tour Packages',  'body' => 'Curated holiday packages with hotels, transfers & sightseeing.',   'url' => 'front.packages'],
                ['icon' => 'fa-van-shuttle',     'title' => 'Transport',      'body' => 'Airport transfers, car rental & intercity transport on demand.',   'url' => 'front.transport'],
            ],

            'home_why' => [
                // The image on the first block is the one the section shows.
                ['icon' => 'fa-bolt',                 'title' => 'Instant confirmations',  'body' => 'Book tours, flights and hotels and get confirmed in minutes.', 'image' => demo_image('1527631746610-bca00a040d60', 'slide')],
                ['icon' => 'fa-hand-holding-dollar',  'title' => 'Best price guarantee',   'body' => 'Transparent pricing with no hidden charges — ever.'],
                ['icon' => 'fa-user-shield',          'title' => 'Secured payments',       'body' => 'Bank-grade encryption and trusted local payment gateways.'],
                ['icon' => 'fa-headset',              'title' => 'Dedicated support',      'body' => 'A real travel expert with you before, during and after the trip.'],
            ],

            'about_values' => [
                ['icon' => 'fa-handshake', 'title' => 'Trust first', 'body' => 'Honest pricing and advice you can rely on, every time.'],
                ['icon' => 'fa-bolt',      'title' => 'Speed',       'body' => 'Fast confirmations and quick visa turnarounds.'],
                ['icon' => 'fa-star',      'title' => 'Care',        'body' => 'A dedicated expert who treats your trip like their own.'],
            ],

            'visa_steps' => [
                ['icon' => 'fa-file-lines',      'title' => 'Submit documents',    'body' => 'Share your passport & papers online or at our office.'],
                ['icon' => 'fa-user-check',      'title' => 'We review & apply',   'body' => 'Our experts verify, fill forms and lodge your application.'],
                ['icon' => 'fa-calendar-check',  'title' => 'Embassy appointment', 'body' => 'We book and guide you through biometrics & interview.'],
                ['icon' => 'fa-passport',        'title' => 'Get your visa',       'body' => 'Collect your approved visa — ready to travel!'],
            ],

            'support_topics' => [
                ['icon' => 'fa-suitcase-rolling',  'title' => 'Bookings',    'body' => 'Manage, modify or track your bookings.',        'url' => 'front.track.booking'],
                ['icon' => 'fa-passport',          'title' => 'Visa help',   'body' => 'Document checklists and application status.',   'url' => 'front.track.visa'],
                ['icon' => 'fa-credit-card',       'title' => 'Payments',    'body' => 'Payment methods, invoices and refunds.',        'url' => 'front.faq'],
                ['icon' => 'fa-circle-question',   'title' => 'General FAQ', 'body' => 'Answers to the most common questions.',         'url' => 'front.faq'],
            ],

            'agent_benefits' => [
                ['icon' => 'fa-percent',     'title' => 'Attractive commissions', 'body' => 'Earn competitive commissions on every booking you make.'],
                ['icon' => 'fa-tags',        'title' => 'Wholesale rates',        'body' => 'Access exclusive B2B fares on flights, hotels and tours.'],
                ['icon' => 'fa-wallet',      'title' => 'Agent wallet',           'body' => 'Top up once and book instantly from a dedicated wallet.'],
                ['icon' => 'fa-chart-line',  'title' => 'Real-time reports',      'body' => 'Track sales, commissions and payouts from your portal.'],
                ['icon' => 'fa-headset',     'title' => 'Priority support',       'body' => 'A dedicated account manager for every partner.'],
            ],

            'umrah_includes' => [
                ['icon' => 'fa-passport',    'title' => 'Umrah visa processing'],
                ['icon' => 'fa-plane',       'title' => 'Return air tickets'],
                ['icon' => 'fa-hotel',       'title' => 'Hotels near the Haram'],
                ['icon' => 'fa-van-shuttle', 'title' => 'Airport & ziyarah transport'],
                ['icon' => 'fa-user-group',  'title' => 'Experienced Bangla-speaking guide'],
            ],

            'hajj_includes' => [
                ['icon' => 'fa-passport',    'title' => 'Hajj visa & government processing'],
                ['icon' => 'fa-plane',       'title' => 'Return air tickets'],
                ['icon' => 'fa-hotel',       'title' => 'Makkah & Madinah accommodation'],
                ['icon' => 'fa-tents',       'title' => 'Mina & Arafat tents'],
                ['icon' => 'fa-user-group',  'title' => 'Trained Bangla-speaking muallim'],
            ],

            'booking_why' => [
                ['title' => 'No upfront payment to enquire'],
                ['title' => 'Best price guarantee'],
                ['title' => '24/7 expert support'],
                ['title' => 'Instant confirmation by phone'],
            ],
        ];
    }
}
