<?php

namespace App\Http\Requests\VehicleCategory;

use Illuminate\Validation\Rule;
use App\Repositories\VehicleCategory\VehicleCategoryRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'       => ['required', 'string', 'max:100', 'unique:vehicle_categories,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status'     => ['required', Rule::in(VehicleCategoryRepository::STATUSES)],
        ];
    }
}
