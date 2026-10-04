<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // name, channel, audience, budget, start offset (days), end offset (days), status
            ['Eid Umrah Early Bird',      'Social',     'Facebook lookalike — Umrah interest', 80000,  -60, -20, 'Completed'],
            ['Winter Beach Getaways',     'Social',     'Past beach-tour customers',           60000,  -10, 35,  'Running'],
            ['Umrah December Push',       'Google Ads', 'Search: umrah package bd',            120000, -5,  50,  'Running'],
            ['Newsletter — Visa Updates', 'Email',      'All subscribers',                     5000,   -30, -2,  'Completed'],
            ['Honeymoon Fair Print Ads',  'Print',      'Wedding fair visitors',               40000,  20,  40,  'Planned'],
            ['SMS Flash Sale',            'SMS',        'Wallet-active customers',             15000,  -15, -8,  'Paused'],
        ];

        foreach ($rows as $r) {
            Campaign::updateOrCreate(
                ['name' => $r[0]],
                [
                    'channel'    => $r[1],
                    'audience'   => $r[2],
                    'budget'     => $r[3],
                    'start_date' => now()->addDays($r[4])->toDateString(),
                    'end_date'   => now()->addDays($r[5])->toDateString(),
                    'status'     => $r[6],
                ]
            );
        }
    }
}
