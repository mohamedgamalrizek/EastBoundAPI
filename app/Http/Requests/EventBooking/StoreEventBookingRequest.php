<?php

namespace App\Http\Requests\EventBooking;

use Illuminate\Validation\Rule;
use App\Repositories\EventBooking\EventBookingRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventBookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'booking_no'     => ['required', 'string', 'max:50', 'unique:event_bookings,booking_no'],
            'event_tour_id'  => ['required', 'exists:event_tours,id'],
            'customer_id'    => ['nullable', 'exists:customers,id'],
            'customer_name'  => ['required', 'string', 'max:100'],
            'seats'          => ['required', 'integer', 'min:1', function ($attribute, $value, $fail) {
                // A cancelled booking holds no seats, so it can always be saved.
                if ($this->input('status') === 'Cancelled') {
                    return;
                }

                $event = \App\Models\EventTour::find($this->input('event_tour_id'));

                if ($event && (int) $value > $event->seatsLeft()) {
                    $fail("Only {$event->seatsLeft()} seat(s) left on {$event->title}.");
                }
            }],
            'amount'         => ['required', 'numeric', 'min:0'],
            'status'         => ['required', Rule::in(EventBookingRepository::STATUSES)],
            'payment_method' => ['nullable', Rule::in(\App\Services\Accounting\BillingService::PAYMENT_METHODS)],
        ];
    }
}
