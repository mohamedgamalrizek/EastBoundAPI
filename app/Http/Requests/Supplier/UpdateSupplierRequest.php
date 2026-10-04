<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Validation\Rule;
use App\Repositories\Supplier\SupplierRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'             => ['required', 'exists:suppliers,id'],
            'name'           => ['required', 'string', 'max:150'],
            'type'           => ['required', Rule::in(SupplierRepository::TYPES)],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'balance'        => ['required', 'numeric'],
            'status'         => ['required', Rule::in(SupplierRepository::STATUSES)],
        ];
    }
}
