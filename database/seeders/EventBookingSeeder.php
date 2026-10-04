<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\EventBooking;
use App\Models\EventTour;

class EventBookingSeeder extends Seeder
{
    public function run(): void
    {
        $events      = EventTour::pluck('id', 'title');
        $customerIds = Customer::pluck('id')->all();

        if ($events->isEmpty()) {
            return;
        }

        // Confirmed/Paid rows invoice themselves via EventBookingObserver.
        // booking_no, event title, customer_name, seats, amount, status, payment_method
        $rows = [
            ['EVB-901', 'Dubai Expo Business Delegation',  'Ayesha Rahman',   2, 190000, 'Paid',      'Bank'],
            ['EVB-902', 'Dubai Expo Business Delegation',  'Imran Chowdhury', 1,  95000, 'Confirmed', null],
            ['EVB-903', 'Singapore FinTech Festival Tour', 'Rifat Karim',     1, 145000, 'Confirmed', null],
            ['EVB-904', 'Canton Fair Sourcing Trip',       'Farzana Akter',   3, 255000, 'Paid',      'Cash'],
            ['EVB-905', 'Leadership Retreat — Bali',       'Nabil Khan',      1, 185000, 'Confirmed', null],
            ['EVB-906', 'ITB Berlin Travel Trade Show',    'Tanvir Hasan',    1, 320000, 'Pending',   null],
            ['EVB-907', 'HR Summit Kuala Lumpur',          'Rumana Begum',    2, 130000, 'Cancelled', null],
        ];

        foreach ($rows as $i => $r) {
            EventBooking::updateOrCreate(
                ['booking_no' => $r[0]],
                [
                    'event_tour_id'  => $events->get($r[1]),
                    'customer_id'    => $customerIds ? $customerIds[$i % count($customerIds)] : null,
                    'customer_name'  => $r[2],
                    'seats'          => $r[3],
                    'amount'         => $r[4],
                    'status'         => $r[5],
                    'payment_method' => $r[6],
                ]
            );
        }
    }
}
