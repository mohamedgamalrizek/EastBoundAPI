<?php

namespace App\Http\Requests\Visa;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UploadVisaDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visa_application_id' => ['required', 'exists:visa_applications,id'],
            'document_type'       => ['required', 'string', 'max:100'],
            'document'            => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'status'              => ['required', Rule::in(['Submitted', 'Verified'])],
        ];
    }
}
