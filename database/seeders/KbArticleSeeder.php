<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KbArticle;
use Illuminate\Support\Arr;

class KbArticleSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = \App\Models\User::pluck('id')->all();

        $rows = [
            ['How to book a package',          'Guide',    'Step-by-step guide to booking a tour package for your customers.',  342, 'published'],
            ['Visa process & documents',       'Guide',    'Required documents and the end-to-end visa application workflow.',   289, 'published'],
            ['Payments & refunds',             'Guide',    'How payments are processed and the policy for issuing refunds.',     256, 'published'],
            ['Agent commissions',              'Guide',    'Understand how agent commissions are calculated and paid out.',      198, 'published'],
            ['Managing customer bookings',     'Guide',    'Edit, reschedule and cancel customer bookings from the dashboard.',  174, 'published'],
            ['Umrah & Hajj packages overview', 'Guide',    'A quick overview of available Umrah and Hajj packages.',             153, 'published'],
            ['Using the reports module',       'Guide',    'Generate sales, booking and accounts reports for any period.',       121, 'published'],
            ['Account & profile settings',     'Guide',    'Update your profile, change password and manage notifications.',      98, 'published'],
        ];

        foreach ($rows as $r) {
            KbArticle::updateOrCreate(
                ['title' => $r[0]],
                [
                    'user_id'  => $userIds ? Arr::random($userIds) : null,
                    'category' => $r[1],
                    'excerpt'  => $r[2],
                    'views'    => $r[3],
                    'status'   => $r[4],
                ]
            );
        }
    }
}
