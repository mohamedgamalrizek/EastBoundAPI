<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

/**
 * An agent amending their own pending booking, from the portal or the app.
 *
 * Only the two things the client can change their mind about. The amount is
 * deliberately absent: it is re-quoted from the package on save, so an agent
 * cannot price their own sale.
 */
class AmendAgentBookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'travel_date' => ['required', 'date', 'after_or_equal:today'],
            'travelers'   => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
}
