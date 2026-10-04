<?php

namespace App\Http\Requests\VehicleCategory;

use Illuminate\Validation\Rule;
use App\Repositories\VehicleCategory\VehicleCategoryRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'         => ['required', 'exists:vehicle_categories,id'],
            'name'       => ['required', 'string', 'max:100', Rule::unique('vehicle_categories', 'name')->ignore($this->input('id'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status'     => ['required', Rule::in(VehicleCategoryRepository::STATUSES)],
        ];
    }
}
