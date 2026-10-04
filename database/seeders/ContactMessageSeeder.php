<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactMessage;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // name, email, phone, subject, message, status
            ['Rafiq Islam',    'rafiq.islam@gmail.com',   '+880 1711 220011', 'Umrah package query',        'Assalamu alaikum, I want details of the Premium Umrah Package for 2 adults in December. Is Madinah hotel walking distance?', 'new'],
            ['Sonia Rahman',   'sonia.rahman@gmail.com',  '+880 1812 330022', 'Maldives honeymoon dates',   'We are getting married in November — do you have Maldives availability for the last week of November? Budget around 3 lakh.', 'new'],
            ['David Costa',    'david.costa@outlook.com', '+880 1913 440033', 'Visa processing time',       'How long does the Schengen tourist visa processing take from Dhaka? I need to travel in about six weeks.', 'read'],
            ['Mahin Alam',     'mahin.alam@yahoo.com',    '+880 1614 550044', 'Group discount',             'We are a group of 12 colleagues planning Cox\'s Bazar next month. Is there a group rate for the beach escape package?', 'read'],
            ['Tania Ferdous',  'tania.f@gmail.com',       '+880 1515 660055', 'Refund status',              'I cancelled booking TRV-1042 last week but have not received the refund in my wallet yet. Please check.', 'replied'],
            ['Kamrul Hasan',   'kamrul.h@gmail.com',      '+880 1316 770066', 'Corporate travel proposal',  'Our company needs yearly ticketing support for approximately 40 employees. Please share a corporate proposal.', 'replied'],
        ];

        foreach ($rows as $r) {
            ContactMessage::updateOrCreate(
                ['email' => $r[1], 'subject' => $r[3]],
                [
                    'name'    => $r[0],
                    'phone'   => $r[2],
                    'message' => $r[4],
                    'status'  => $r[5],
                ]
            );
        }
    }
}
