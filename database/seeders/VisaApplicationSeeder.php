<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Package;
use App\Models\VisaApplication;

class VisaApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $customerIds = Customer::pluck('id')->all();
        $packageIds  = Package::pluck('id')->all();

        // The service being applied for carries the government and service fee,
        // so an application without it cannot price itself. Country names are
        // spelled differently on the two tables, hence the map.
        $serviceAliases = [
            'UAE'          => 'United Arab Emirates',
            'Schengen'     => 'Schengen (Europe)',
            'UK'           => 'United Kingdom',
            'USA'          => 'United States',
        ];
        $services = \App\Models\VisaService::get(['id', 'country', 'visa_type']);

        // Embassy/centre, time, appointment status and notes are seeded too —
        // without them the Embassy Appointment screen renders blank columns.
        //
        // Dates are offsets in days from the day you seed, never hard-coded:
        // fixed dates drift, and the Expiry Management screen — whose whole job
        // is "what expires soon" — then shows nothing in any band forever.
        // The expiry offsets deliberately land one row in each band
        // (<=15, 16-30, 31-90) so the screen demonstrates itself, plus one
        // already-expired row for the renewal-lead list.
        $day = fn (?int $offset) => $offset === null ? null : now()->startOfDay()->addDays($offset)->toDateString();

        // [no, applicant, country, type, applied, status, docs, appointment, expiry, centre, time, appt status, notes]
        $rows = [
            // Only an Approved case carries an expiry date — that is the whole
            // rule Expiry Management runs on, so the demo data has to obey it.
            ['VISA-3301', 'Sadia Islam',      'UAE',          'Tourist',  -75, 'Approved',   'Verified',  -66,  10,   'UAE Embassy, Dhaka',      '10:30', 'Completed',   'Visa issued — collect passport.'],
            ['VISA-3300', 'Imran Chowdhury',  'Schengen',     'Business', -79, 'Approved',   'Verified',  -71,  24,   'VFS Schengen, Gulshan',   '09:00', 'Completed',   'Carry original invitation letter.'],
            ['VISA-3299', 'Farzana Akter',    'Saudi Arabia', 'Umrah',    -81, 'In Review',  'Submitted', -68,  null, 'Saudi Consulate, Dhaka',  '11:15', 'Pending',     'Awaiting slot confirmation.'],
            ['VISA-3298', 'Rifat Karim',      'Malaysia',     'Tourist',  -85, 'Rejected',   'Pending',   null, null, null,                      null,    null,          null],
            ['VISA-3297', 'Nabil Khan',       'Thailand',     'Tourist',  -87, 'Approved',   'Verified',  -77,  78,   'Thai Embassy, Dhaka',     '14:00', 'Completed',   'Moved at applicant request.'],
            ['VISA-3296', 'Mitu Akter',       'UAE',          'Tourist',  -89, 'Processing', 'Submitted', -74,  null, 'UAE Embassy, Dhaka',      '12:45', 'Confirmed',   null],
            ['VISA-3295', 'Tanvir Hasan',     'Canada',       'Student',  -92, 'In Review',  'Submitted', -64,  null, 'VFS Canada, Banani',      '09:45', 'Pending',     'Study permit interview.'],
            ['VISA-3294', 'Rumana Begum',     'UK',           'Business', -95, 'Approved',   'Verified',  -82,  -12,  'VFS UK, Dhaka',           '15:30', 'Completed',   'Expired — renewal lead.'],
        ];

        foreach ($rows as $i => $r) {
            $r[4] = $day($r[4]);
            $r[7] = $day($r[7]);
            $r[8] = $day($r[8]);

            VisaApplication::updateOrCreate(
                ['application_no' => $r[0]],
                [
                    'customer_id'        => $customerIds ? $customerIds[$i % count($customerIds)] : null,
                    'visa_service_id'    => $services
                        ->first(fn ($s) => $s->country === ($serviceAliases[$r[2]] ?? $r[2]) && $s->visa_type === $r[3])?->id
                        ?? $services->first(fn ($s) => $s->country === ($serviceAliases[$r[2]] ?? $r[2]))?->id,
                    'package_id'         => $packageIds  ? $packageIds[$i  % count($packageIds)]  : null,
                    'applicant_name'     => $r[1],
                    'country'            => $r[2],
                    'visa_type'          => $r[3],
                    'applied_date'       => $r[4],
                    'status'             => $r[5],
                    'documents_status'   => $r[6],
                    'appointment_date'   => $r[7],
                    'expiry_date'        => $r[8],
                    'embassy_center'     => $r[9],
                    'appointment_time'   => $r[10],
                    'appointment_status' => $r[11],
                    'appointment_notes'  => $r[12],
                ]
            );
        }
    }
}
