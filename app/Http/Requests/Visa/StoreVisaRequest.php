<?php

namespace App\Http\Requests\Visa;

use Illuminate\Validation\Rule;
use App\Repositories\Visa\VisaRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id'      => ['nullable', 'exists:customers,id'],
            'package_id'       => ['nullable', 'exists:packages,id'],
            'visa_service_id'  => ['nullable', 'exists:visa_services,id'],
            'govt_fee'         => ['nullable', 'numeric', 'min:0'],
            'service_fee'      => ['nullable', 'numeric', 'min:0'],
            'application_no'   => ['required', 'string', 'max:50', 'unique:visa_applications,application_no'],
            'applicant_name'   => ['required', 'string', 'max:100'],
            'country'          => ['required', 'string', 'max:100'],
            'visa_type'        => ['required', Rule::in(VisaRepository::VISA_TYPES)],
            'applied_date'     => ['nullable', 'date'],
            'appointment_date' => ['nullable', 'date'],
            'expiry_date'      => ['nullable', 'date', 'after_or_equal:applied_date'],
            'documents_status' => ['required', Rule::in(VisaRepository::DOCUMENT_STATUSES)],
            'status'           => ['required', Rule::in(VisaRepository::STATUSES)],
        ];
    }
}
