<?php

namespace App\Http\Requests\Driver;

use Illuminate\Validation\Rule;
use App\Repositories\Driver\DriverRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'         => ['required', 'exists:drivers,id'],
            'name'       => ['required', 'string', 'max:150'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'license_no' => ['nullable', 'string', 'max:50', Rule::unique('drivers', 'license_no')->ignore($this->input('id'))],
            'vehicle'    => ['nullable', 'string', 'max:100'],
            'status'     => ['required', Rule::in(DriverRepository::STATUSES)],
        ];
    }
}
