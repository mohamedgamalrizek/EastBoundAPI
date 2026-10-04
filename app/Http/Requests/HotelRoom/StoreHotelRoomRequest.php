<?php

namespace App\Http\Requests\HotelRoom;

use Illuminate\Validation\Rule;
use App\Repositories\HotelRoom\HotelRoomRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRoomRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hotel_id'        => ['required', 'exists:hotels,id'],
            'room_type'       => ['required', 'string', 'max:100'],
            'capacity'        => ['required', 'integer', 'min:1'],
            'rate_per_night'  => ['required', 'numeric', 'min:0'],
            'total_rooms'     => ['required', 'integer', 'min:0'],
            'available_rooms' => ['required', 'integer', 'min:0'],
            'status'          => ['required', Rule::in(HotelRoomRepository::STATUSES)],
        ];
    }
}
