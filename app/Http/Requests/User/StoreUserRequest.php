<?php

namespace App\Http\Requests\User;

use App\Enums\Gender;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'name'          => ['required', 'string', 'min:4', 'max:50'],
            'email'         => ['required', 'email', 'max:191', 'unique:users,email'],
            'date_of_birth'           => ['required', 'date', 'before:today'],
            'gender'        => 'required|' . Rule::in(array_column(Gender::cases(), 'value')),
            'phone'         => ['required', 'regex:/^(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})$/', 'unique:users,phone'],
            // 'phone'         => 'required|regex:/^\+?[0-9]{1,4}-?[0-9]{7,14}$/|unique:users,phone,',
            'role_id'       => ['required', 'exists:roles,id'],
            'password'      => ['required', 'string', 'min:8'],
            'nid_number'    => ['nullable', 'numeric', 'digits_between:3,20'],
            'status'        => ['required', 'boolean'],

            'nid'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5098',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5098',
        ];
    }

    public function messages()
    {
        return [
            'gender.required'  => 'Please select a gender.'
        ];
    }
}
