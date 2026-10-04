<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Models\Customer;
use Illuminate\Support\Arr;

class HotelBookingSeeder extends Seeder
{
    public function run(): void
    {
        $hotels      = Hotel::all()->keyBy(fn ($h) => $h->city . '|' . $h->name);
        $customerIds = Customer::pluck('id')->all();

        if ($hotels->isEmpty()) {
            return;
        }

        // [ booking_no, hotelKey(city|name), guest_name, room_type, check_in, nights, status, payment_method ]
        // Confirmed rows invoice themselves via HotelBookingObserver; Paid
        // rows also get a receipt in the method shown.
        $rows = [
            ['HB-5100', 'Makkah|Hilton Makkah',      'Abdullah Rahman',  'Deluxe King',     '2026-06-12', 4, 'Paid',      'Card'],
            ['HB-5101', 'Dubai|Atlantis The Palm',   'Sara Khan',        'Ocean Suite',     '2026-06-15', 3, 'Confirmed', null],
            ['HB-5102', 'Makkah|Pullman Zamzam',     'Imran Hossain',    'Haram View',      '2026-06-18', 5, 'Booked',    null],
            ['HB-5103', 'Makkah|Swissotel Al Maqam', 'Fatima Begum',     'Premium King',    '2026-06-20', 2, 'Confirmed', null],
            ['HB-5104', 'Madinah|Movenpick Madinah', 'Yusuf Ali',        'Family Room',     '2026-06-22', 4, 'Paid',      'Bank'],
            ['HB-5105', 'Dhaka|Le Meridien Dhaka',   'Nadia Akter',      'Executive Suite', '2026-06-24', 1, 'Booked',    null],
            ['HB-5106', "Cox's Bazar|Sea Pearl Cox", 'Tanvir Ahmed',     'Sea View Deluxe', '2026-06-25', 3, 'Confirmed', null],
            ['HB-5107', 'Sylhet|Grand Sylhet',       'Rumana Sultana',   'Standard Room',   '2026-06-27', 2, 'Cancelled', null],
            ['HB-5108', 'Dubai|Atlantis The Palm',   'Omar Faruk',       'Royal Bridge',    '2026-06-28', 2, 'Confirmed', null],
            ['HB-5109', 'Makkah|Hilton Makkah',      'Ayesha Siddiqua',  'Twin Standard',   '2026-07-01', 6, 'Confirmed', null],
            ['HB-5110', 'Madinah|Movenpick Madinah', 'Karim Uddin',      'Superior Twin',   '2026-07-03', 3, 'Booked',    null],
            ['HB-5111', 'Dhaka|Le Meridien Dhaka',   'Shahin Alam',      'Executive Suite', '2026-07-05', 2, 'Confirmed', null],
        ];

        foreach ($rows as $r) {
            $hotel    = $hotels->get($r[1]);
            $checkIn  = Carbon::parse($r[4]);
            $nights   = $r[5];
            $checkOut = $checkIn->copy()->addDays($nights);
            $rate     = $hotel ? (float) $hotel->price_per_night : 10000.00;

            // Resolve hotel_room_id: prefer a room matching the intended room type for this hotel, else any room.
            $roomIds = HotelRoom::pluck('id')->all();
            $room    = $hotel ? HotelRoom::where('hotel_id', $hotel->id)->where('room_type', $r[3])->first() : null;
            $roomId  = $room?->id ?? ($roomIds ? Arr::random($roomIds) : null);

            if (! $roomId) {
                continue;
            }

            HotelBooking::updateOrCreate(
                ['booking_no' => $r[0]],
                [
                    'hotel_id'      => $hotel?->id,
                    'customer_id'   => $customerIds ? Arr::random($customerIds) : null,
                    'hotel_room_id' => $roomId,
                    'guest_name'    => $r[2],
                    'check_in'      => $checkIn->toDateString(),
                    'check_out'     => $checkOut->toDateString(),
                    'nights'        => $nights,
                    'amount'        => $rate * $nights,
                    'status'        => $r[6],
                    'payment_method' => $r[7],
                ]
            );
        }
    }
}
