<?php

namespace Modules\Saas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DomainRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'domain'    => ['required', 'string', 'max:150', 'unique:domains,domain'],
        ];
    }
}
