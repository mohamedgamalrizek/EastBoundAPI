<?php

namespace App\Http\Requests\CorporateTravel;

use Illuminate\Validation\Rule;
use App\Repositories\CorporateTravel\CorporateTravelRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreCorporateTravelRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'company_name' => ['required', 'string', 'max:191'],
            'contact_person' => ['nullable', 'string', 'max:191'],
            'service_type' => ['required', Rule::in(CorporateTravelRepository::SERVICE_TYPE)],
            'employees' => ['nullable', 'integer'],
            'budget' => ['nullable', 'numeric'],
            'status' => ['required', Rule::in(CorporateTravelRepository::STATUS)],
        ];
    }
}
