<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CrmActivity;
use Illuminate\Support\Arr;

class CrmActivitySeeder extends Seeder
{
    public function run(): void
    {
        $customerIds = \App\Models\Customer::pluck('id')->all();
        $userIds     = \App\Models\User::pluck('id')->all();
        // Activities are matched to the lead of the same name where one
        // exists — that link is what the lead timeline reads.
        $leadsByName = \App\Models\Lead::pluck('id', 'name');
        $leadIds     = $leadsByName->values()->all();

        $rows = [
            // type, customer_name, subject, body, activity_date, channel
            // ---- Follow-ups ----
            ['followup', 'Mahmud Hasan',  'Call Mahmud',          'Discuss revised Maldives package and confirm dates.',         '2026-06-03', 'Call'],
            ['followup', 'Nusrat Jahan',  'Umrah proposal',       'Send Umrah group proposal for 40 pilgrims.',                  '2026-06-07', 'Email'],
            ['followup', 'Sadia Islam',   'Visa docs due',        'Collect remaining visa documents before appointment.',        '2026-06-12', 'Meeting'],
            ['followup', 'Ayesha Rahman', 'Site visit',           'Schedule resort site walkthrough with client.',               '2026-06-18', 'Meeting'],
            ['followup', 'Tanvir Hasan',  'Payment reminder',     'Follow up on outstanding corporate invoice (Net-15).',        '2026-06-22', 'Call'],
            ['followup', 'Rumana Begum',  'Tour briefing',        'Pre-departure briefing for Turkey Grand Tour.',               '2026-06-27', 'Meeting'],

            // ---- Activity timeline ----
            ['activity', 'Mahmud Hasan',  'Proposal sent',        'Sami sent a proposal to Mahmud Hasan.',                       '2026-06-08', 'Email'],
            ['activity', 'Rumana Begum',  'Call logged',          'Lima logged a call with Rumana Begum.',                       '2026-06-08', 'Call'],
            ['activity', 'Karim Sheikh',  'New lead created',     'New lead "Karim Sheikh" created.',                            '2026-06-07', null],
            ['activity', 'Tanvir Hasan',  'Payment received',     'Payment BDT 72,000 received from Tanvir.',                    '2026-06-07', null],
            ['activity', 'Sadia Islam',   'Visa documents uploaded','Visa documents uploaded for Sadia.',                        '2026-06-07', null],
            ['activity', 'Nusrat Jahan',  'Umrah quote requested','Nusrat Jahan requested Umrah group quote.',                   '2026-06-05', null],

            // ---- Notes ----
            ['note', 'Ayesha Rahman', 'Travel preferences',  'Prefers window seats and halal meals. Travels with spouse.',      '2026-06-05', null],
            ['note', 'Tanvir Hasan',  'Corporate billing',   'Corporate client — invoice to company. Net-15 terms.',            '2026-06-04', null],
            ['note', 'Nusrat Jahan',  'Group accessibility', 'Umrah group of 40. Needs wheelchair assistance for 2 pilgrims.',  '2026-06-03', null],
            ['note', 'Rifat Karim',   'Pricing note',        'Price sensitive. Follow up with off-season Bali deal.',           '2026-06-01', null],

            // ---- Communication ----
            ['communication', 'Ayesha Rahman', 'Proposal: Maldives 5D/4N', 'Sent proposal email — status: Sent.',               '2026-06-05', 'Email'],
            ['communication', 'Rumana Begum',  'Outbound call — 6 min',    'Outbound call completed — status: Completed.',        '2026-06-05', 'Call'],
            ['communication', 'Rifat Karim',   'Payment reminder sent',    'Payment reminder SMS — status: Delivered.',           '2026-06-04', 'SMS'],
            ['communication', 'Shirin Akhter', 'Shared Dubai itinerary',   'WhatsApp itinerary shared — status: Read.',           '2026-06-04', 'WhatsApp'],
            ['communication', 'Sadia Islam',   'Visa document checklist',  'Checklist email — status: Opened.',                   '2026-06-03', 'Email'],
        ];

        foreach ($rows as $r) {
            CrmActivity::firstOrCreate(
                [
                    'type'          => $r[0],
                    'customer_name' => $r[1],
                    'subject'       => $r[2],
                ],
                [
                    'customer_id'   => $customerIds ? Arr::random($customerIds) : null,
                    'user_id'       => $userIds ? Arr::random($userIds) : null,
                    'lead_id'       => $leadsByName[$r[1]]
                        ?? ($leadIds ? $leadIds[crc32($r[1]) % count($leadIds)] : null),
                    'body'          => $r[3],
                    'activity_date' => $r[4],
                    'channel'       => $r[5],
                ]
            );
        }
    }
}
