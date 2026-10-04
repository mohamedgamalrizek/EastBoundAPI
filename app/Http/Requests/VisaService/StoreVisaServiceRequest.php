<?php

namespace App\Http\Requests\VisaService;

use Illuminate\Validation\Rule;
use App\Repositories\VisaService\VisaServiceRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisaServiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'country'         => ['required', 'string', 'max:255'],
            'flag'            => ['nullable', 'string', 'max:20'],
            'visa_type'       => ['required', Rule::in(VisaServiceRepository::TYPES)],
            'processing_time' => ['nullable', 'string', 'max:100'],
            'stay_duration'   => ['nullable', 'string', 'max:100'],
            'entry_type'      => ['nullable', Rule::in(\App\Models\VisaService::ENTRY_TYPES)],
            'govt_fee'        => ['nullable', 'numeric', 'min:0'],
            'service_fee'     => ['nullable', 'numeric', 'min:0'],
            'requirements'    => ['nullable', 'string', 'max:3000'],
            'is_featured'     => ['nullable', 'boolean'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'status'          => ['required', Rule::in(VisaServiceRepository::STATUSES)],
        ];
    }
}
