<?php

namespace App\Http\Requests\Flight;

use Illuminate\Validation\Rule;
use App\Repositories\Flight\FlightRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFlightRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'             => ['required', 'exists:flight_bookings,id'],
            'customer_id'    => ['nullable', 'exists:customers,id'],
            'booking_id'     => ['nullable', 'exists:bookings,id'],
            'pnr'            => ['required', 'string', 'max:50', 'unique:flight_bookings,pnr,' . $this->input('id')],
            'passenger_name' => ['required', 'string', 'max:100'],
            'airline'        => ['required', 'string', 'max:100'],
            'route'          => ['required', 'string', 'max:100'],
            'flight_date'    => ['required', 'date'],
            'ticket_no'      => ['nullable', 'string', 'max:50'],
            'fare'           => ['required', 'numeric', 'min:0'],
            'status'         => ['required', Rule::in(FlightRepository::STATUSES)],
        ];
    }
}
