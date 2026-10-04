<?php

namespace Modules\Saas\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Saas\Http\Controllers\SaasController;
use Illuminate\Foundation\Http\FormRequest;

class TenantRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'      => ['sometimes', 'required', 'exists:tenants,id'],
            'name'    => ['required', 'string', 'max:150'],
            'email'   => ['required', 'email', 'max:150'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'status'  => ['required', Rule::in(SaasController::TENANT_STATUSES)],
            // domain only on create; unique across the central domains table.
            'domain'  => ['nullable', 'string', 'max:150', 'unique:domains,domain'],
        ];
    }
}
