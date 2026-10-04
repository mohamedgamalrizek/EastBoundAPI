<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

/**
 * An agent booking a package for their client, from the portal or the app.
 *
 * No amount field: the fare is quoted from the catalogue in
 * CreateAgentBooking, so an agent cannot price their own sale.
 */
class StoreAgentBookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'package_id'   => ['required', 'integer', 'exists:packages,id'],
            'client_name'  => ['required', 'string', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'travel_date'  => ['required', 'date', 'after_or_equal:today'],
            'travelers'    => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
}
