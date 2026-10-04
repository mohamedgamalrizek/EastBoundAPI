<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\FlightBooking;

class FlightBookingSeeder extends Seeder
{
    public function run(): void
    {
        $customerIds = Customer::pluck('id')->all();
        $bookingIds  = Booking::pluck('id')->all();

        // pnr, passenger_name, airline, route, flight_date, ticket_no, fare, status
        $rows = [
            ['XQ7P2L', 'Ayesha Rahman',   'Emirates', 'DAC→DXB',     '2026-06-12', '157-2401887', 48000.00, 'Confirmed'],
            ['LM3K9A', 'Tanvir Hasan',    'Qatar',    'DAC→DOH',     '2026-06-14', '157-2401886', 43500.00, 'Confirmed'],
            ['PP1209', 'Nusrat Jahan',    'Saudia',   'DAC→JED',     '2026-06-15', '157-2401884', 52000.00, 'Confirmed'],
            ['BG4471', 'Imran Chowdhury', 'Biman',    'DAC→DXB',     '2026-06-18', '157-2401883', 39000.00, 'Confirmed'],
            ['QR8810', 'Sadia Islam',     'Qatar',    'DAC→LHR',     '2026-06-20', '157-2401882', 92000.00, 'Confirmed'],
            ['HX2204', 'Rumana Begum',    'Emirates', 'DAC→JFK',     '2026-06-22', null,          118000.00, 'Pending'],
            ['VN3390', 'Farzana Akter',   'Biman',    'DAC→KUL',     '2026-06-25', null,          31000.00, 'Pending'],
            ['ZK5567', 'Rifat Karim',     'Malaysia', 'DAC→KUL',     '2026-06-10', '157-2401885', 31000.00, 'Cancelled'],
            ['WP9921', 'Mitu Akter',      'Saudia',   'DAC→RUH',     '2026-06-08', '157-2401852', 48500.00, 'Cancelled'],
            ['TY6648', 'Nabil Khan',      'Thai',     'DAC→BKK',     '2026-06-05', '157-2401840', 27000.00, 'Refunded'],
            ['UJ1133', 'Shahriar Alam',   'Emirates', 'DAC→DXB',     '2026-06-02', '157-2401838', 47000.00, 'Refunded'],
            ['RC7705', 'Sabrina Haque',   'Qatar',    'DAC→DOH',     '2026-06-28', '157-2401890', 45000.00, 'Reissued'],
        ];

        foreach ($rows as $i => $r) {
            FlightBooking::updateOrCreate(
                ['pnr' => $r[0]],
                [
                    'customer_id'    => $customerIds ? $customerIds[$i % count($customerIds)] : null,
                    'booking_id'     => $bookingIds  ? $bookingIds[$i  % count($bookingIds)]  : null,
                    'passenger_name' => $r[1],
                    'airline'        => $r[2],
                    'route'          => $r[3],
                    'flight_date'    => $r[4],
                    'ticket_no'      => $r[5],
                    'fare'           => $r[6],
                    'status'         => $r[7],
                ]
            );
        }
    }
}
