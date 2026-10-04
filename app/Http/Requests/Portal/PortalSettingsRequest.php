<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PortalSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'                => ['required', 'string', 'max:100'],
            'language'            => ['required', Rule::in(['en', 'bn'])],
            'currency'            => ['required', Rule::in(['BDT', 'USD'])],
            'email_notifications' => ['nullable', 'boolean'],
            'sms_alerts'          => ['nullable', 'boolean'],
        ];
    }
}
