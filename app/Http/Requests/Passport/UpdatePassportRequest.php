<?php

namespace App\Http\Requests\Passport;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePassportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'          => ['required', 'exists:passports,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'holder_name' => ['required', 'string', 'max:100'],
            'passport_no' => ['required', 'string', 'max:50', Rule::unique('passports', 'passport_no')->ignore($this->input('id'))],
            'nationality' => ['nullable', 'string', 'max:100'],
            'issue_date'  => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
        ];
    }
}
