<?php

namespace Modules\Saas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'plan_id'   => ['required', 'exists:plans,id'],
            'company'   => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'max:150'],
            'subdomain' => ['required', 'string', 'alpha_dash', 'min:3', 'max:40'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages()
    {
        return [
            'subdomain.alpha_dash' => 'Subdomain may only contain letters, numbers, dashes and underscores.',
        ];
    }
}
