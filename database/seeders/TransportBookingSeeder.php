<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\TransportBooking;

class TransportBookingSeeder extends Seeder
{
    public function run(): void
    {
        $customerIds = Customer::pluck('id')->all();
        $bookingIds  = Booking::pluck('id')->all();
        // Cars and airport transfers are driven by the agency's own drivers;
        // bus/train/launch tickets are not, so those keep a null driver.
        $driverIds   = \App\Models\Driver::pluck('id')->all();

        // Statuses come from TransportRepository::STATUSES — the billing
        // observer invoices Confirmed/Paid/Completed rows and receipts the
        // Paid ones in their payment method.
        $rows = [
            // booking_no, type, customer_name, route, travel_date, vehicle, fare, status, payment_method
            ['BUS-201', 'Bus',     'Ayesha Rahman',  "Dhaka → Cox's Bazar",      '2026-06-12', 'Green Line (AC Sleeper)',      3400.00, 'Confirmed', null],
            ['BUS-200', 'Bus',     'Imran Chowdhury', 'Dhaka → Sylhet',           '2026-06-14', 'Hanif (AC)',                   1200.00, 'Paid',      'bKash'],
            ['BUS-199', 'Bus',     'Farzana Akter',   'Dhaka → Khulna',           '2026-06-15', 'Shyamoli (Non-AC)',            1600.00, 'Pending',   null],

            ['TRN-118', 'Train',   'Rifat Karim',     'Dhaka → Chattogram',       '2026-06-12', 'Subarna Express (S_Chair)',    1150.00, 'Confirmed', null],
            ['TRN-117', 'Train',   'Nabil Khan',      'Dhaka → Sylhet',           '2026-06-13', 'Parabat Express (AC Cabin)',   2300.00, 'Paid',      'Cash'],
            ['TRN-116', 'Train',   'Mitu Akter',      'Dhaka → Dinajpur',         '2026-06-16', 'Ekota Express (Shovan)',        620.00, 'Pending',   null],

            ['LCH-77',  'Launch',  'Tanvir Hasan',    'Dhaka → Barishal',         '2026-06-12', 'Sundarban-16 (VIP Cabin)',     4200.00, 'Confirmed', null],
            ['LCH-76',  'Launch',  'Rumana Begum',    'Dhaka → Bhola',            '2026-06-13', 'Adventure-1 (Single Cabin)',   1800.00, 'Paid',      'Nagad'],
            ['LCH-75',  'Launch',  'Sadia Islam',     'Dhaka → Patuakhali',       '2026-06-14', 'Parabat-12 (Deck)',             450.00, 'Pending',   null],

            ['CAR-310', 'Car',     'Sohel Rana',      'Dhaka City (Self-drive)',  '2026-06-12', 'Toyota Premio',               12000.00, 'Confirmed', null],
            ['CAR-309', 'Car',     'Jamal Hossain',   'Dhaka City (With Driver)', '2026-06-13', 'Toyota Hiace',                18000.00, 'Booked',    null],

            ['AT-440',  'Airport', 'Ayesha Rahman',   'DAC Airport → Gulshan',    '2026-06-12', 'Toyota Premio (CAR-310)',      2500.00, 'Confirmed', null],
            ['AT-439',  'Airport', 'Tanvir Hasan',    'Banani → DAC Airport',     '2026-06-13', 'Land Cruiser (CAR-308)',       3500.00, 'Completed', null],
            ['AT-438',  'Airport', 'Nusrat Jahan',    'DAC Airport → Uttara',     '2026-06-14', 'Toyota Hiace',                 3000.00, 'Pending',   null],
        ];

        foreach ($rows as $i => $r) {
            TransportBooking::updateOrCreate(
                ['booking_no' => $r[0]],
                [
                    'customer_id'   => $customerIds ? $customerIds[$i % count($customerIds)] : null,
                    'booking_id'    => $bookingIds  ? $bookingIds[$i  % count($bookingIds)]  : null,
                    'driver_id'     => $driverIds && in_array($r[1], ['Car', 'Airport'], true)
                        ? $driverIds[$i % count($driverIds)]
                        : null,
                    'type'          => $r[1],
                    'customer_name' => $r[2],
                    'route'         => $r[3],
                    'travel_date'   => $r[4],
                    'vehicle'       => $r[5],
                    'fare'          => $r[6],
                    'status'        => $r[7],
                    'payment_method' => $r[8],
                ]
            );
        }
    }
}
