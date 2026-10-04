<?php

namespace App\Http\Requests\Visa;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVisaDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'            => ['required', 'exists:visa_documents,id'],
            'document_type' => ['required', 'string', 'max:100'],
            'document'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'status'        => ['required', Rule::in(['Submitted', 'Verified'])],
        ];
    }
}
