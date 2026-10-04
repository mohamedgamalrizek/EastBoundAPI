<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'package_id'     => ['nullable', 'exists:packages,id'],
            'customer_id'    => ['required', 'integer', 'exists:customers,id'],
            'agent_id'       => ['nullable', 'integer', 'exists:users,id'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'travel_date'    => ['required', 'date'],
            'travelers'      => ['required', 'integer', 'min:1'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'status'         => ['required', 'in:pending,confirmed,paid,cancelled'],
            // Recorded on the receipt when the booking is marked paid.
            'payment_method' => ['nullable', 'in:Cash,Bank,Card,bKash,Nagad,Wallet'],
            'notes'          => ['nullable', 'string'],
            // The code is looked up, not validated against a table: an
            // unknown or spent one is reported back and the booking saves at
            // full price rather than the form being thrown away.
            'coupon_code'    => ['nullable', 'string', 'max:60'],
        ];
    }
}
