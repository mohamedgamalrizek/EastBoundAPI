<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Validation\Rule;
use App\Repositories\Coupon\CouponRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'id' => ['required', 'exists:coupons,id'],
            'code' => ['required', 'string', 'max:191'],
            'type' => ['required', Rule::in(CouponRepository::TYPE)],
            'value' => ['required', 'numeric'],
            'min_spend' => ['nullable', 'numeric'],
            'usage_limit' => ['nullable', 'integer'],
            'expires_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(CouponRepository::STATUS)],
        ];
    }
}
