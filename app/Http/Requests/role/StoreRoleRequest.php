<?php

namespace App\Http\Requests\role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize() 
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'name'          => ['required', 'string', 'unique:roles,name', 'max:60'],
            'status'        => ['required', 'numeric'],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['string'],
        ];
    }

    public function messages()
    {
        return [
            'permissions.required' => ___('alert.select_at_least_one_permission'),
            'permissions.min'      => ___('alert.select_at_least_one_permission'),
        ];
    }
}
