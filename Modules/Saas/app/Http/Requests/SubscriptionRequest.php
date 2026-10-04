<?php

namespace Modules\Saas\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Saas\Http\Controllers\SaasController;
use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'        => ['sometimes', 'required', 'exists:subscriptions,id'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'plan_id'   => ['required', 'exists:plans,id'],
            'status'    => ['required', Rule::in(SaasController::SUBSCRIPTION_STATUSES)],
            'starts_at' => ['nullable', 'date'],
            'ends_at'   => ['nullable', 'date', 'after_or_equal:starts_at'],
            'amount'    => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
