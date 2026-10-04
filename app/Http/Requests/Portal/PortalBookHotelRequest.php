<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class PortalBookHotelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hotel_id'      => ['required', 'exists:hotels,id'],
            'hotel_room_id' => ['required', 'exists:hotel_rooms,id'],
            'check_in'      => ['required', 'date', 'after_or_equal:today'],
            'check_out'     => ['required', 'date', 'after:check_in'],
            'guest_name'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
