<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupportTicket;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        // Demo portal customer first so the customer portal's Support page is
        // not empty for the account people log in with.
        $customerIds = \App\Models\Customer::orderByRaw("FIELD(email, 'customer@bugbuild.com') DESC")
            ->orderBy('id')->pluck('id')->all();
        $userIds     = \App\Models\User::pluck('id')->all();
        // Some tickets are handled by the agent, whose portal lists the ones
        // assigned to them plus any raised by their own clients.
        $agentIds    = \App\Models\User::whereHas('role', fn ($q) => $q->where('name', 'Agent'))->pluck('id')->all();

        $rows = [
            ['TKT-4101', 'Refund not received',        'Rifat Karim',     'High',   'Refund',   'Open'],
            ['TKT-4100', 'Change travel date',         'Tanvir Hasan',    'Medium', 'Booking',  'Pending'],
            ['TKT-4099', 'Visa status query',          'Sadia Islam',     'Low',    'Visa',     'Closed'],
            ['TKT-4098', 'Hotel upgrade request',      'Imran Chowdhury', 'Medium', 'Booking',  'Open'],
            ['TKT-4097', 'Invoice correction needed',  'Farzana Akter',   'High',   'Billing',  'Pending'],
            ['TKT-4096', 'Lost baggage claim',         'Nabil Khan',      'High',   'Support',  'Open'],
            ['TKT-4095', 'Umrah package details',      'Mitu Akter',      'Low',    'Sales',    'Closed'],
            ['TKT-4094', 'Payment gateway error',      'Rumana Begum',    'High',   'Billing',  'Open'],
            ['TKT-4093', 'Agent commission inquiry',   'Shahin Alam',     'Medium', 'Accounts', 'Pending'],
            ['TKT-4092', 'Flight reschedule',          'Nusrat Jahan',    'Medium', 'Booking',  'Closed'],
        ];

        foreach ($rows as $i => $r) {
            SupportTicket::updateOrCreate(
                ['ticket_no' => $r[0]],
                [
                    'customer_id'   => $customerIds ? $customerIds[$i % 3 === 0 ? 0 : ($i % count($customerIds))] : null,
                    'assigned_to'   => $i % 3 === 1 && $agentIds
                        ? $agentIds[$i % count($agentIds)]
                        : ($userIds ? $userIds[$i % count($userIds)] : null),
                    'subject'       => $r[1],
                    'customer_name' => $r[2],
                    'priority'      => $r[3],
                    'department'    => $r[4],
                    'status'        => $r[5],
                ]
            );
        }
    }
}
