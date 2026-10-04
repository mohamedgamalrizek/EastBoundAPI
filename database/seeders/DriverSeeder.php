<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\TransportBooking;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // name, phone, license_no, vehicle, status, [booking_no linked to this driver...]
            ['Habibur Rahman', '+880 1711-223344', 'DL-BD-2019-00417', 'Toyota Premio',  'Active',   ['CAR-310', 'AT-440']],
            ['Mizanur Islam',  '+880 1819-556677', 'DL-BD-2020-01822', 'Toyota Hiace',    'Active',   ['CAR-309', 'AT-438']],
            ['Selim Sarker',   '+880 1922-334455', 'DL-BD-2018-00931', 'Land Cruiser',    'Active',   ['AT-439']],
            ['Kamal Uddin',    '+880 1611-889900', 'DL-BD-2021-02765', 'Toyota Noah',     'On Leave', []],
            ['Rafiqul Islam',  '+880 1533-112233', 'DL-BD-2017-00204', 'Nissan X-Trail',  'Inactive', []],
        ];

        foreach ($rows as $r) {
            $driver = Driver::updateOrCreate(
                ['license_no' => $r[2]],
                [
                    'name'    => $r[0],
                    'phone'   => $r[1],
                    'vehicle' => $r[3],
                    'status'  => $r[4],
                ]
            );

            TransportBooking::whereIn('booking_no', $r[5])->update(['driver_id' => $driver->id]);
        }
    }
}
