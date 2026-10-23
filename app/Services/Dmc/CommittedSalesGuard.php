<?php

namespace App\Services\Dmc;

use App\Models\Booking;
use App\Models\Dmc\SalesOrder;
use App\Models\Invoice;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CommittedSalesGuard
{
    public function booking(Booking $booking, bool $deleting = false): void
    {
        if (! Schema::hasTable('dmc_sales_orders') || ! SalesOrder::where('booking_id', $booking->id)->exists()) {
            return;
        }
        if ($deleting || $booking->isDirty(['amount', 'gross_amount', 'customer_id', 'lead_id', 'travel_date', 'travelers', 'coupon_id', 'coupon_code', 'points_redeemed'])
            || ($booking->isDirty('status') && ! in_array($booking->status, ['confirmed', 'paid']))) {
            throw ValidationException::withMessages(['workflow' => 'This booking belongs to an accepted DMC sales order. Approved commercial terms cannot be rewritten through legacy booking forms.']);
        }
    }

    public function invoice(Invoice $invoice, bool $deleting = false): void
    {
        if (! $invoice->booking_id || ! Schema::hasTable('dmc_sales_orders') || ! SalesOrder::where('booking_id', $invoice->booking_id)->exists()) {
            return;
        }
        if ($deleting || $invoice->isDirty(['amount', 'customer_id', 'booking_id'])) {
            throw ValidationException::withMessages(['workflow' => 'An accepted DMC invoice must retain its approved sale and customer. Record payments or refunds through accounting.']);
        }
    }
}
