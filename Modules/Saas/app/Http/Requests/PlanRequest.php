<?php

namespace Modules\Saas\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Saas\Http\Controllers\SaasController;
use Illuminate\Foundation\Http\FormRequest;

class PlanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'            => ['sometimes', 'required', 'exists:plans,id'],
            'name'          => ['required', 'string', 'max:100'],
            'price'         => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', Rule::in(SaasController::BILLING_CYCLES)],
            'max_users'     => ['nullable', 'integer', 'min:1'],
            'features'      => ['nullable', 'string', 'max:2000'],
            'status'        => ['required', 'boolean'],
        ];
    }
}
