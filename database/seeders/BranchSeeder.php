<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // name, code, manager_name, phone, email, city, address, status
            ['Head Office — Banani',   'HQ-DHK', 'Shafiqul Islam',  '+880 9610 111000', 'headoffice@flow.com',  'Dhaka',      'House 42, Road 11, Banani, Dhaka 1213',        'active'],
            ['Uttara Branch',          'BR-UTR', 'Nadia Hossain',   '+880 9610 111001', 'uttara@flow.com',      'Dhaka',      'Plot 7, Sector 7, Rabindra Sarani, Uttara',    'active'],
            ['Chattogram Branch',      'BR-CTG', 'Mizanur Rahman',  '+880 9610 111002', 'ctg@flow.com',         'Chattogram', 'Ayub Trade Centre, Agrabad C/A',               'active'],
            ['Sylhet Branch',          'BR-SYL', 'Abdul Karim',     '+880 9610 111003', 'sylhet@flow.com',      'Sylhet',     'Westworld Shopping City, Zindabazar',          'active'],
            ['Khulna Branch',          'BR-KHL', 'Rokeya Begum',    '+880 9610 111004', 'khulna@flow.com',      'Khulna',     'Tiger Garden Road, Khulna Sadar',              'inactive'],
        ];

        foreach ($rows as $r) {
            Branch::updateOrCreate(
                ['code' => $r[1]],
                [
                    'name'         => $r[0],
                    'manager_name' => $r[2],
                    'phone'        => $r[3],
                    'email'        => $r[4],
                    'city'         => $r[5],
                    'address'      => $r[6],
                    'status'       => $r[7],
                ]
            );
        }
    }
}
