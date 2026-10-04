<?php

namespace App\Http\Requests\ContactMessage;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $choice = $this->input('subject_choice');
        $custom = trim((string) $this->input('subject_custom'));

        if ($choice || $custom) {
            $this->merge([
                'subject' => $choice === 'Other' && $custom !== '' ? $custom : $choice,
            ]);
        }
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'subject_choice' => ['nullable', 'string', 'max:255'],
            'subject_custom' => ['nullable', 'string', 'max:255'],
            'subject'        => ['nullable', 'string', 'max:255'],
            'message'        => ['required', 'string', 'max:5000'],
        ];
    }
}
