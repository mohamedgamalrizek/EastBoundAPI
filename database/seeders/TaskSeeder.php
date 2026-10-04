<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use Illuminate\Support\Arr;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = \App\Models\User::pluck('id')->all();

        $rows = [
            // title, project, priority, due_date, status
            ['Confirm Maldives hotel',       'Maldives Group', 'High',   '2026-06-10', 'In Progress'],
            ['Collect visa docs',            'UAE Visas',      'Medium', '2026-06-09', 'Todo'],
            ['Issue Umrah tickets',          'Umrah June',     'High',   '2026-06-12', 'Done'],
            ['Update website banner',        'Marketing Q3',   'Low',    '2026-06-15', 'Todo'],
            ['Prepare Q3 marketing report',  'Marketing Q3',   'Medium', '2026-06-18', 'Todo'],
            ['Umrah hotel allocation',       'Umrah June',     'High',   '2026-06-11', 'In Progress'],
            ['Book Green Line coach',        'Umrah June',     'Low',    '2026-06-05', 'Done'],
            ['Verify UAE visa payments',     'UAE Visas',      'Medium', '2026-06-14', 'In Progress'],
            ['Maldives airport transfer',    'Maldives Group', 'Medium', '2026-06-13', 'Todo'],
            ['Design Eid promo creatives',   'Marketing Q3',   'High',   '2026-06-20', 'Todo'],
            ['Reconcile Umrah invoices',     'Umrah June',     'Medium', '2026-06-07', 'Done'],
            ['Maldives final itinerary',     'Maldives Group', 'High',   '2026-06-09', 'In Progress'],
        ];

        foreach ($rows as $r) {
            Task::updateOrCreate(
                ['title' => $r[0]],
                [
                    'assigned_to' => $userIds ? Arr::random($userIds) : null,
                    'project'     => $r[1],
                    'priority'    => $r[2],
                    'due_date'    => $r[3],
                    'status'      => $r[4],
                ]
            );
        }
    }
}
