<?php

namespace App\Http\Requests\HotelBooking;

use Illuminate\Validation\Rule;
use App\Repositories\HotelBooking\HotelBookingRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHotelBookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'            => ['required', 'exists:hotel_bookings,id'],
            'booking_no'    => ['required', 'string', 'max:50', Rule::unique('hotel_bookings', 'booking_no')->ignore($this->input('id'))],
            'hotel_id'      => ['required', 'exists:hotels,id'],
            'customer_id'   => ['nullable', 'exists:customers,id'],
            'agent_id'      => ['nullable', 'integer', 'exists:users,id'],
            'hotel_room_id' => ['required', 'exists:hotel_rooms,id'],
            'guest_name'    => ['required', 'string', 'max:100'],
            'check_in'      => ['required', 'date'],
            'check_out'     => ['required', 'date', 'after_or_equal:check_in'],
            'nights'        => ['required', 'integer', 'min:0'],
            'amount'        => ['required', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(HotelBookingRepository::STATUSES)],
            'payment_method' => ['nullable', Rule::in(\App\Services\Accounting\BillingService::PAYMENT_METHODS)],
        ];
    }
}
