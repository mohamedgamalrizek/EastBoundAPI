<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $packageIds  = Package::pluck('id')->all();
        if (empty($packageIds)) {
            return; // packages must be seeded first
        }
        $customerIds = Customer::pluck('id')->all();

        // Users carrying the Agent role. Two thirds of the demo bookings are
        // credited to an agent so the agent portal (bookings, customers,
        // commissions, reports) has something to show; the rest are direct
        // office sales, which is what a NULL agent_id means.
        $agentIds = \App\Models\User::whereHas('role', fn ($q) => $q->where('name', 'Agent'))
            ->pluck('id')->all();

        // bookings.status is enum('pending','confirmed','paid','cancelled')
        $rows = [
            // customer_name, email, phone, travel_date, travelers, amount, status
            ['Ayesha Rahman',   'ayesha@example.com',  '01710000001', '2026-06-12', 2, 115000, 'confirmed'],
            ['Tanvir Hasan',    'tanvir@example.com',  '01710000002', '2026-06-20', 1, 48000,  'paid'],
            ['Nusrat Jahan',    'nusrat@example.com',  '01710000003', '2026-07-20', 4, 320000, 'pending'],
            ['Rifat Karim',     'rifat@example.com',   '01710000004', '2026-05-04', 2, 86000,  'paid'],
            ['Sadia Islam',     'sadia@example.com',   '01710000005', '2026-06-01', 1, 9000,   'pending'],
            ['Imran Chowdhury', 'imran@example.com',   '01710000006', '2026-08-10', 3, 210000, 'confirmed'],
            ['Farzana Akter',   'farzana@example.com', '01710000007', '2026-05-26', 2, 175000, 'cancelled'],
            ['Nabil Khan',      'nabil@example.com',   '01710000008', '2026-05-20', 1, 52000,  'paid'],
            ['Rumana Begum',    'rumana@example.com',  '01710000009', '2026-06-15', 2, 98000,  'confirmed'],
            ['Mitu Akter',      'mitu@example.com',    '01710000010', '2026-07-02', 5, 412000, 'pending'],
            ['Shakil Ahmed',    'shakil@example.com',  '01710000011', '2026-04-18', 2, 76000,  'paid'],
            ['Tahmina Sultana', 'tahmina@example.com', '01710000012', '2026-06-28', 3, 240000, 'confirmed'],
            ['Ayesha Rahman',   'ayesha@example.com',  '01710000001', '2026-09-05', 2, 132000, 'pending'],
            ['Imran Chowdhury', 'imran@example.com',   '01710000006', '2026-03-30', 1, 39000,  'paid'],
            ['Nabil Khan',      'nabil@example.com',   '01710000008', '2026-07-15', 4, 288000, 'confirmed'],
        ];

        foreach ($rows as $i => $r) {
            Booking::updateOrCreate(
                ['customer_email' => $r[1], 'travel_date' => $r[3]],
                [
                    'customer_id'    => $customerIds ? $customerIds[$i % count($customerIds)] : null,
                    'agent_id'       => $agentIds && $i % 3 !== 2 ? $agentIds[$i % count($agentIds)] : null,
                    'package_id'     => $packageIds[$i % count($packageIds)],
                    'customer_name'  => $r[0],
                    'customer_phone' => $r[2],
                    'travelers'      => $r[4],
                    'amount'         => $r[5],
                    'status'         => $r[6],
                    'notes'          => 'Seeded demo booking.',
                ]
            );
        }
    }
}
