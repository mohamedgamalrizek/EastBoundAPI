<?php

namespace App\Http\Requests\EventBooking;

use Illuminate\Validation\Rule;
use App\Repositories\EventBooking\EventBookingRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventBookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'             => ['required', 'exists:event_bookings,id'],
            'booking_no'     => ['required', 'string', 'max:50', Rule::unique('event_bookings', 'booking_no')->ignore($this->input('id'))],
            'event_tour_id'  => ['required', 'exists:event_tours,id'],
            'customer_id'    => ['nullable', 'exists:customers,id'],
            'customer_name'  => ['required', 'string', 'max:100'],
            'seats'          => ['required', 'integer', 'min:1', function ($attribute, $value, $fail) {
                if ($this->input('status') === 'Cancelled') {
                    return;
                }

                $event = \App\Models\EventTour::find($this->input('event_tour_id'));

                if (! $event) {
                    return;
                }

                // This booking's own live seats are already counted in
                // seatsBooked(), so they come back into the pool first.
                $own  = \App\Models\EventBooking::find($this->input('id'));
                $held = ($own && $own->event_tour_id === $event->id && $own->status !== 'Cancelled')
                    ? (int) $own->seats : 0;

                if ((int) $value > $event->seatsLeft() + $held) {
                    $fail('Only ' . ($event->seatsLeft() + $held) . " seat(s) left on {$event->title}.");
                }
            }],
            'amount'         => ['required', 'numeric', 'min:0'],
            'status'         => ['required', Rule::in(EventBookingRepository::STATUSES)],
            'payment_method' => ['nullable', Rule::in(\App\Services\Accounting\BillingService::PAYMENT_METHODS)],
        ];
    }
}
