<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Demo customers. Every row gets a known password so the mobile app can log
     * in straight after a reseed — previously these were password-less and the
     * app's test account had to be created by hand (and was lost on any
     * migrate:fresh). Demo-only: this seeder runs behind APP_DEMO.
     */
    public const DEMO_PASSWORD = 'secret123';

    public function run(): void
    {
        $rows = [
            ['Ayesha Rahman',   'ayesha@example.com',   '01710000001', 'Gulshan, Dhaka',      'Platinum', 'active'],
            ['Tanvir Hasan',    'tanvir@example.com',   '01710000002', 'Dhanmondi, Dhaka',    'Gold',     'active'],
            ['Nusrat Jahan',    'nusrat@example.com',   '01710000003', 'Banani, Dhaka',       'Silver',   'active'],
            ['Rifat Karim',     'rifat@example.com',    '01710000004', 'Uttara, Dhaka',       'Gold',     'active'],
            ['Sadia Islam',     'sadia@example.com',    '01710000005', 'Mirpur, Dhaka',       'Silver',   'active'],
            ['Imran Chowdhury', 'imran@example.com',    '01710000006', 'Chattogram',          'Platinum', 'active'],
            ['Farzana Akter',   'farzana@example.com',  '01710000007', 'Sylhet',              'Silver',   'inactive'],
            ['Nabil Khan',      'nabil@example.com',    '01710000008', 'Khulna',              'Gold',     'active'],
            ['Rumana Begum',    'rumana@example.com',   '01710000009', 'Rajshahi',            'Gold',     'active'],
            ['Mitu Akter',      'mitu@example.com',     '01710000010', 'Bogura',              'Silver',   'active'],
            ['Shakil Ahmed',    'shakil@example.com',   '01710000011', 'Cumilla',             'Gold',     'inactive'],
            ['Tahmina Sultana', 'tahmina@example.com',  '01710000012', 'Barishal',            'Platinum', 'active'],

            // The mobile app's documented test login.
            ['Sadia Islam',     'sadia@test.com',       '01710000099', 'Mirpur, Dhaka',       'Gold',     'active'],
        ];

        foreach ($rows as $r) {
            Customer::updateOrCreate(
                ['email' => $r[1]],
                [
                    'name'     => $r[0],
                    'phone'    => $r[2],
                    'address'  => $r[3],
                    'tier'     => $r[4],
                    'status'   => $r[5],
                    'password' => Hash::make(self::DEMO_PASSWORD),
                    'notes'    => 'Seeded demo customer.',
                ]
            );
        }
    }
}
