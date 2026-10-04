<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Invoice;

/**
 * Demo invoices, each tied to a real booking (and through it to that booking's
 * customer) rather than to a random id — otherwise the invoice cannot be found
 * from the customer's own portal.
 *
 * No status is seeded: saving an invoice re-derives it from its receipts.
 */
class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = Booking::orderBy('id')->get();

        // invoice_no, billing name, issue_date, due_date, amount
        $rows = [
            ['INV-1041', 'Rahim Travels Ltd.',     '2026-06-05', '2026-06-20', 115000],
            ['INV-1040', 'Karim Hossain',          '2026-06-04', '2026-06-19', 48000],
            ['INV-1039', 'Sky Voyage Agency',      '2026-06-03', '2026-06-18', 9000],
            ['INV-1038', 'Global Corporate Tours', '2026-06-02', '2026-06-17', 320000],
            ['INV-1037', 'Nadia Sultana',          '2026-06-01', '2026-06-16', 86000],
            ['INV-1036', 'Umrah Pilgrims Co.',     '2026-05-30', '2026-06-14', 27000],
            ['INV-1035', 'BlueSky Holidays',       '2026-05-28', '2026-06-12', 500000],
            ['INV-1034', 'Tanvir Ahmed',           '2026-05-25', '2026-06-09', 64000],
            ['INV-1033', 'Meghna Tours',           '2026-05-20', '2026-06-04', 142000],
            ['INV-1032', 'Farzana Akter',          '2026-05-18', '2026-06-02', 38500],
            ['INV-1031', 'Padma Travel House',     '2026-05-15', '2026-05-30', 97000],
            ['INV-1030', 'Rezaul Karim',           '2026-05-12', '2026-05-27', 21000],
        ];

        foreach ($rows as $i => $r) {
            $booking = $bookings->get($i % max($bookings->count(), 1));

            Invoice::updateOrCreate(
                ['invoice_no' => $r[0]],
                [
                    'booking_id'    => $booking?->id,
                    'customer_id'   => $booking?->customer_id,
                    'customer_name' => $r[1],
                    'issue_date'    => $r[2],
                    'due_date'      => $r[3],
                    'amount'        => $r[4],
                ]
            );
        }
    }
}
