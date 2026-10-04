<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class PortalPassportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // id is only present on update; ownership is enforced in the repository.
            'id'          => ['sometimes', 'required', 'integer'],
            'holder_name' => ['required', 'string', 'max:100'],
            'passport_no' => ['required', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'issue_date'  => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
        ];
    }
}
