<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // name, phone, email, interest, source, value, stage, owner
            ['Rashed Karim',    '+880 1711 000101', 'rashed.karim@gmail.com',   'Umrah Package',            'Facebook',  165000, 'New',         'Sales Desk'],
            ['Nusrat Jahan',    '+880 1811 000102', 'nusrat.j@gmail.com',       'Maldives Honeymoon',       'Website',   145000, 'Contacted',   'Sales Desk'],
            ['Tanvir Ahmed',    '+880 1911 000103', 'tanvir.a@yahoo.com',       'Dubai City & Desert',      'WhatsApp',  89000,  'Proposal',    'Farhan'],
            ['Sabbir Hossain',  '+880 1611 000104', 'sabbir.h@gmail.com',       'Student Visa - Canada',    'Referral',  250000, 'Negotiation', 'Farhan'],
            ['Mehnaz Sultana',  '+880 1511 000105', 'mehnaz.s@gmail.com',       'Thailand Family Tour',     'Instagram', 152000, 'Won',         'Sales Desk'],
            ['Arif Chowdhury',  '+880 1311 000106', 'arif.c@outlook.com',       'Schengen Tourist Visa',    'Walk-in',   35000,  'Contacted',   'Rumana'],
            ['Farzana Akter',   '+880 1712 000107', 'farzana.akter@gmail.com',  'Kashmir Valley Tour',      'Facebook',  64000,  'New',         'Rumana'],
            ['Imran Kabir',     '+880 1813 000108', 'imran.kabir@gmail.com',    'Corporate Retreat - Bali', 'Website',   560000, 'Proposal',    'Farhan'],
            ['Sharmin Nahar',   '+880 1914 000109', 'sharmin.n@gmail.com',      'Hajj Pre-registration',    'Referral',  480000, 'Negotiation', 'Sales Desk'],
            ['Jubayer Rahman',  '+880 1615 000110', 'jubayer.r@gmail.com',      'Istanbul Heritage Trail',  'WhatsApp',  98000,  'Lost',        'Rumana'],
        ];

        foreach ($rows as $r) {
            Lead::updateOrCreate(
                ['name' => $r[0]],
                [
                    'phone'    => $r[1],
                    'email'    => $r[2],
                    'interest' => $r[3],
                    'source'   => $r[4],
                    'value'    => $r[5],
                    'stage'    => $r[6],
                    'owner'    => $r[7],
                    'notes'    => 'Demo lead seeded for the CRM pipeline board.',
                ]
            );
        }
    }
}
