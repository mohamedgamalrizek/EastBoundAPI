<?php

namespace App\Http\Requests\MedicalTour;

use Illuminate\Validation\Rule;
use App\Repositories\MedicalTour\MedicalTourRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalTourRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'id' => ['required', 'exists:medical_tours,id'],
            'patient_name' => ['required', 'string', 'max:191'],
            'destination' => ['required', 'string', 'max:191'],
            'hospital' => ['nullable', 'string', 'max:191'],
            'treatment' => ['nullable', 'string', 'max:191'],
            'cost' => ['nullable', 'numeric'],
            'status' => ['required', Rule::in(MedicalTourRepository::STATUS)],
        ];
    }
}
