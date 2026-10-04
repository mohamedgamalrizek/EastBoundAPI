<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffAttendance;

class StaffAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Rows are spread over the staff users, always starting with the demo
        // Staff login — otherwise the staff portal (which shows only your own
        // rows) opens empty for the account people actually sign in with.
        $staffIds = \App\Models\User::orderByRaw("FIELD(email, 'staff@bugbuild.com') DESC")
            ->orderBy('id')
            ->pluck('id')->all();

        $rows = [
            // staff_name,  date,         check_in, check_out, hours, status
            ['Sami Rahman', '2026-06-05', '09:02', '18:10', 9.13, 'Present'],
            ['Sami Rahman', '2026-06-04', '09:15', '18:05', 8.83, 'Present'],
            ['Sami Rahman', '2026-06-03', null,    null,    null,  'Leave'],
            ['Sami Rahman', '2026-06-02', '09:05', '18:00', 8.92, 'Present'],
            ['Sami Rahman', '2026-06-01', '09:10', '18:20', 9.17, 'Present'],
            ['Sami Rahman', '2026-05-29', '09:00', '17:55', 8.92, 'Present'],
            ['Sami Rahman', '2026-05-28', null,    null,    null,  'Absent'],
            ['Sami Rahman', '2026-05-27', '09:20', '18:15', 8.92, 'Present'],
            ['Sami Rahman', '2026-05-26', '09:08', '18:02', 8.90, 'Present'],
            ['Sami Rahman', '2026-05-25', '09:12', '18:08', 8.93, 'Present'],
        ];

        foreach ($rows as $r) {
            StaffAttendance::updateOrCreate(
                ['staff_name' => $r[0], 'date' => $r[1]],
                [
                    'user_id'   => $staffIds[0] ?? null,
                    'check_in'  => $r[2],
                    'check_out' => $r[3],
                    'hours'     => $r[4],
                    'status'    => $r[5],
                ]
            );
        }
    }
}
