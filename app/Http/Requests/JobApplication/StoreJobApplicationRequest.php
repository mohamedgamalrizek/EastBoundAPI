<?php

namespace App\Http\Requests\JobApplication;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['nullable', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'linkedin_url'  => ['nullable', 'url', 'max:255'],
            'resume'        => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'cover_letter'  => ['nullable', 'string', 'max:5000'],
        ];
    }
}
