<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // name, type, contact_person, phone, email, balance, status
            ['Emirates',          'Airline',   'Sales Desk',     '+880 9610 998811', 'sales@emirates.com',     1200000, 'active'],
            ['Qatar Airways',     'Airline',   'Corp Sales',     '+880 9610 998822', 'bd@qatarairways.com',    800000,  'active'],
            ['Biman Bangladesh',  'Airline',   'Corporate Desk', '+880 9610 998833', 'corp@biman.gov.bd',      0,       'active'],

            ['Hilton Makkah',     'Hotel',     'Reservations',   '+966 12 5345555',  'res@hilton.com',         420000,  'active'],
            ['Atlantis The Palm', 'Hotel',     'Sales Office',   '+971 4 4260000',   'sales@atlantis.com',     210000,  'active'],
            ['Pullman Zamzam',    'Hotel',     'Booking Desk',   '+966 12 5710000',  'book@pullman.com',       0,       'active'],

            ['Green Line',        'Transport', 'Operations',     '+880 1730 060009', 'ops@greenline.com',      120000,  'active'],
            ['City Cab',          'Transport', 'Hire Desk',      '+880 1711 223344', 'hire@citycab.com',       45000,   'active'],
            ['Sundarban Launch',  'Transport', 'Booking',        '+880 1733 445566', 'book@sundarban.com',     0,       'inactive'],

            ['VFS Global',        'Visa',      'Dhaka Office',    '+880 9606 777111', 'dhaka@vfs.com',          240000,  'active'],
            ['Al Madina Travels', 'Visa',      'Visa Desk',       '+880 9606 777222', 'info@almadina.com',      110000,  'active'],
            ['Gulf Visa Services','Visa',      'Support',         '+880 9606 777333', 'help@gulfvisa.com',      0,       'inactive'],
        ];

        foreach ($rows as $r) {
            Supplier::updateOrCreate(
                ['name' => $r[0]],
                [
                    'type'           => $r[1],
                    'contact_person' => $r[2],
                    'phone'          => $r[3],
                    'email'          => $r[4],
                    'balance'        => $r[5],
                    'status'         => $r[6],
                ]
            );
        }
    }
}
