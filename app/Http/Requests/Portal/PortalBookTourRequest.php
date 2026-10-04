<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class PortalBookTourRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'package_id'  => ['required', 'exists:packages,id'],
            'travel_date' => ['required', 'date', 'after_or_equal:today'],
            'travelers'   => ['required', 'integer', 'min:1', 'max:50'],
            // Optional, and never fatal: an unusable code or an unaffordable
            // number of points prices the booking without them and says so,
            // rather than throwing the form back at the customer.
            'coupon_code'     => ['nullable', 'string', 'max:60'],
            'points_redeemed' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
