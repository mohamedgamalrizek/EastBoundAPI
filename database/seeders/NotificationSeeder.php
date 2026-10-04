<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = \App\Models\User::pluck('id')->all();
        // The customer portal reads notifications addressed to the Customer
        // record (notifiable) as well as ones addressed to the login itself,
        // so half of the demo rows are aimed at the portal customer.
        $customer = \App\Models\Customer::where('email', 'customer@bugbuild.com')->first()
            ?? \App\Models\Customer::first();
        $portalUserId = \App\Models\User::where('email', 'customer@bugbuild.com')->value('id');

        // title, body, category, is_read
        $rows = [
            ['Booking confirmed',  'Maldives Luxury Escape',     'booking', false],
            ['Payment due',        'INV-8800',                    'payment', false],
            ['Visa under review',  'VISA-3301',                   'visa',    false],
            ['Ticket issued',      'DAC→DXB · Emirates',          'booking', true],
            ['Refund processed',   'Wallet credited ৳5,000',      'payment', true],
            ['Document verified',  'Passport scan approved',      'general', true],
        ];

        foreach ($rows as $i => $r) {
            Notification::updateOrCreate(
                ['title' => $r[0], 'body' => $r[1]],
                [
                    'user_id'         => $i % 2 === 0
                        ? ($portalUserId ?: ($userIds ? $userIds[0] : null))
                        : ($userIds ? $userIds[$i % count($userIds)] : null),
                    'notifiable_type' => $i % 2 === 0 && $customer ? \App\Models\Customer::class : null,
                    'notifiable_id'   => $i % 2 === 0 && $customer ? $customer->id : null,
                    'category'        => $r[2],
                    'is_read'  => $r[3],
                ]
            );
        }
    }
}
