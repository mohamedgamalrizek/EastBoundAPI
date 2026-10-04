<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveRequest;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Always includes the demo Staff login first: the staff portal shows
        // only your own rows, so seeding these against random users left that
        // account looking at empty pages.
        $staffIds = \App\Models\User::orderByRaw("FIELD(email, 'staff@bugbuild.com') DESC")
            ->orderBy('id')
            ->pluck('id')->all();

        $rows = [
            // staff_name,  leave_type,      from_date,    to_date,      days, reason,                 status
            ['Sami Rahman', 'Casual Leave',  '2026-06-15', '2026-06-16', 2, 'Family event',           'Approved'],
            ['Sami Rahman', 'Sick Leave',    '2026-05-22', '2026-05-22', 1, 'Fever',                  'Approved'],
            ['Sami Rahman', 'Annual Leave',  '2026-07-01', '2026-07-05', 5, 'Vacation',               'Pending'],
            ['Sami Rahman', 'Casual Leave',  '2026-04-10', '2026-04-10', 1, 'Personal work',          'Approved'],
            ['Sami Rahman', 'Sick Leave',    '2026-03-18', '2026-03-19', 2, 'Flu',                    'Approved'],
            ['Sami Rahman', 'Annual Leave',  '2026-02-01', '2026-02-03', 3, 'Trip',                   'Rejected'],
            ['Sami Rahman', 'Casual Leave',  '2026-06-25', '2026-06-25', 1, 'Appointment',            'Pending'],
            ['Sami Rahman', 'Sick Leave',    '2026-01-15', '2026-01-16', 2, 'Medical',                'Approved'],
        ];

        foreach ($rows as $r) {
            LeaveRequest::updateOrCreate(
                ['staff_name' => $r[0], 'leave_type' => $r[1], 'from_date' => $r[2]],
                [
                    'user_id' => $staffIds[0] ?? null,
                    'to_date' => $r[3],
                    'days'    => $r[4],
                    'reason'  => $r[5],
                    'status'  => $r[6],
                ]
            );
        }
    }
}
