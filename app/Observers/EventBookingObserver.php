<?php

namespace App\Observers;

use App\Models\EventBooking;
use App\Services\Accounting\BillingService;

/**
 * Event-tour seats sell like any other service: confirm → invoice, pay →
 * receipt — and the event's Open/Full badge follows what is actually sold.
 */
class EventBookingObserver
{
    public function __construct(protected BillingService $billing) {}

    public function saved(EventBooking $booking): void
    {
        $this->billing->syncEventBooking($booking);
        $booking->eventTour?->syncAvailability();
    }

    public function deleted(EventBooking $booking): void
    {
        $this->billing->withdrawUnpaidInvoiceFor($booking);
        $booking->eventTour?->syncAvailability();
    }
}
