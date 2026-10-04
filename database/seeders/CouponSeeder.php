<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // code, type, value, min_spend, usage_limit, expires_at (days from now), description, status
            ['WELCOME10',   'Percentage', 10,    20000,  100,  90,   'Welcome offer — 10% off your first booking.',              'active'],
            ['EID2026',     'Percentage', 15,    50000,  200,  45,   'Eid special — 15% off all tour packages.',                 'active'],
            ['UMRAH5K',     'Fixed',      5000,  100000, 50,   120,  'Flat ৳5,000 off Umrah packages above ৳1,00,000.',          'active'],
            ['HONEYMOON20', 'Percentage', 20,    120000, 30,   60,   'Honeymoon packages — 20% off for couples.',                'active'],
            ['FLYHIGH2K',   'Fixed',      2000,  30000,  150,  30,   'Flat ৳2,000 off any flight booking.',                      'active'],
            ['WINTER25',    'Percentage', 25,    80000,  40,   null, 'Expired winter campaign coupon kept for reporting.',       'inactive'],
        ];

        foreach ($rows as $r) {
            Coupon::updateOrCreate(
                ['code' => $r[0]],
                [
                    'type'        => $r[1],
                    'value'       => $r[2],
                    'min_spend'   => $r[3],
                    'usage_limit' => $r[4],
                    'expires_at'  => $r[5] ? now()->addDays($r[5])->toDateString() : now()->subDays(20)->toDateString(),
                    'description' => $r[6],
                    'status'      => $r[7],
                ]
            );
        }
    }
}
