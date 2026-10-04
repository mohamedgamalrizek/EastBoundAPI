<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;
use Illuminate\Support\Arr;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = \App\Models\User::pluck('id')->all();

        $rows = [
            ['Eid Holiday Schedule',       'Offices closed Jun 16–18. Emergency support available 24/7.',         'All',       '2026-06-12', 'active'],
            ['New Umrah packages live',    '2026 Umrah packages are now open for booking.',                       'Agents',    '2026-06-05', 'active'],
            ['System maintenance',         'Scheduled maintenance Jun 10, 2–4 AM. The portal may be unavailable.', 'Staff',     '2026-06-08', 'active'],
            ['Updated refund policy',      'Refund processing time reduced to 5 business days effective July 1.',  'Customers', '2026-06-01', 'active'],
            ['Commission rate revision',   'Agent commission rates revised for the new fiscal year.',              'Agents',    '2026-05-20', 'active'],
            ['Year-end closing notice',    'Accounts will be locked for year-end closing on Jun 30.',              'Staff',     '2026-05-15', 'archived'],
        ];

        foreach ($rows as $r) {
            Announcement::updateOrCreate(
                ['title' => $r[0]],
                [
                    'user_id'      => $userIds ? Arr::random($userIds) : null,
                    'body'         => $r[1],
                    'audience'     => $r[2],
                    'published_on' => $r[3],
                    'status'       => $r[4],
                ]
            );
        }
    }
}
