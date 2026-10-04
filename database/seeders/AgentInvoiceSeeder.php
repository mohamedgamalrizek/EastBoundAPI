<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgentInvoice;
use Illuminate\Support\Arr;

class AgentInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        // Agents are Users carrying the Agent role — looked up by name so a
        // reordered roles table cannot silently point this at someone else.
        $agentIds = \App\Models\User::whereHas('role', fn ($q) => $q->where('name', 'Agent'))->pluck('id')->all();
        if (!$agentIds) $agentIds = \App\Models\User::pluck('id')->all();

        // A paid agent invoice carries how and when it was settled — the
        // observer posts it (Cr Sales) and, for Wallet, debits the agent's
        // wallet statement next to their commissions and payouts. Only the
        // small one settles from the wallet: the wallet holds commission
        // money (a few thousand), so wallet-settling a six-figure invoice
        // would just drive the statement negative.
        // invoice_no, customer_name, amount, issued_on, due_on, status, method, paid_on
        $rows = [
            ['INV-A201', 'Ayesha Rahman',   115000, '2026-06-12', '2026-06-26', 'paid',    'Bank',   '2026-06-20'],
            ['INV-A200', 'Rifat Karim',     86000,  '2026-06-08', '2026-06-22', 'paid',    'Bank',   '2026-06-18'],
            ['INV-A199', 'Mitu Akter',      9000,   '2026-06-02', '2026-06-16', 'unpaid',  null,     null],
            ['INV-A198', 'Imran Chowdhury', 156000, '2026-05-28', '2026-06-11', 'paid',    'Bank',   '2026-06-05'],
            ['INV-A197', 'Farzana Akter',   93000,  '2026-05-24', '2026-06-07', 'overdue', null,     null],
            ['INV-A196', 'Nabil Khan',      130000, '2026-05-20', '2026-06-03', 'paid',    'Cash',   '2026-05-30'],
            ['INV-A195', 'Tanvir Hasan',    47000,  '2026-05-16', '2026-05-30', 'overdue', null,     null],
            ['INV-A194', 'Rumana Begum',    72000,  '2026-05-12', '2026-05-26', 'unpaid',  null,     null],
            ['INV-A193', 'Sadia Islam',     4500,   '2026-07-20', '2026-08-03', 'paid',    'Wallet', '2026-08-01'],
        ];

        foreach ($rows as $r) {
            AgentInvoice::updateOrCreate(
                ['invoice_no' => $r[0]],
                [
                    'agent_id'      => $agentIds ? Arr::random($agentIds) : null,
                    'customer_name' => $r[1],
                    'amount'        => $r[2],
                    'issued_on'     => $r[3],
                    'due_on'        => $r[4],
                    'status'        => $r[5],
                    'method'        => $r[6],
                    'paid_on'       => $r[7],
                ]
            );
        }
    }
}
