<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['How do I book a package?',       'Choose a package, click book, and complete the payment to confirm.', 'Booking',  'active'],
            ['What is your refund policy?',    'Refunds depend on the supplier terms and the cancellation window.',   'Payments', 'active'],
            ['Do you offer Umrah packages?',   'Yes, we offer flexible Umrah packages throughout the year.',          'Hajj',     'active'],
            ['Can I customize my itinerary?',  'Absolutely, contact our team to tailor any tour to your needs.',      'Booking',  'active'],
            ['Which payment methods do you accept?', 'We accept cards, bank transfers, and mobile wallets.',          'Payments', 'active'],
            ['Do you arrange travel insurance?', 'Yes, travel insurance can be added during checkout.',               'General',  'active'],
            ['How early should I apply for a visa?', 'We recommend applying at least 4 weeks before departure.',       'Visa',     'active'],
            ['Are group discounts available?', 'Group discounts apply for parties of 6 or more travelers.',           'General',  'inactive'],
        ];

        foreach ($rows as $r) {
            Faq::updateOrCreate(
                ['question' => $r[0]],
                [
                    'answer'   => $r[1],
                    'category' => $r[2],
                    'status'   => $r[3],
                ]
            );
        }
    }
}
