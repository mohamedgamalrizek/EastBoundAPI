<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Validation\Rule;
use App\Repositories\Insurance\InsuranceRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreInsuranceRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'provider' => ['required', 'string', 'max:191'],
            'plan_name' => ['required', 'string', 'max:191'],
            'type' => ['required', Rule::in(InsuranceRepository::TYPE)],
            'coverage' => ['nullable', 'numeric'],
            'premium' => ['nullable', 'numeric'],
            'status' => ['required', Rule::in(InsuranceRepository::STATUS)],
        ];
    }
}
