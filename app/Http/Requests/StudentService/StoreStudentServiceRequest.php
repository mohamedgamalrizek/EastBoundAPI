<?php

namespace App\Http\Requests\StudentService;

use Illuminate\Validation\Rule;
use App\Repositories\StudentService\StudentServiceRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentServiceRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'student_name' => ['required', 'string', 'max:191'],
            'university' => ['nullable', 'string', 'max:191'],
            'country' => ['nullable', 'string', 'max:191'],
            'service_type' => ['required', Rule::in(StudentServiceRepository::SERVICE_TYPE)],
            'status' => ['required', Rule::in(StudentServiceRepository::STATUS)],
        ];
    }
}
