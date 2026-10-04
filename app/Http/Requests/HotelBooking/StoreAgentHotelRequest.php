<?php

namespace App\Http\Requests\HotelBooking;

use Illuminate\Foundation\Http\FormRequest;

/**
 * An agent selling a hotel stay to their client, from the portal or the app.
 *
 * No amount field: the stay is priced server-side in CreateAgentHotelBooking
 * from the room's published rate and the number of nights, so an agent cannot
 * price their own sale.
 */
class StoreAgentHotelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hotel_id'     => ['required', 'integer', 'exists:hotels,id'],
            'hotel_room_id' => ['required', 'integer', 'exists:hotel_rooms,id'],
            'client_name'  => ['required', 'string', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'check_in'     => ['required', 'date', 'after_or_equal:today'],
            'check_out'    => ['required', 'date', 'after:check_in'],
        ];
    }
}
