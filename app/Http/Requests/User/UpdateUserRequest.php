<?php

namespace App\Http\Requests\User;

use App\Enums\Gender;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'id'            => ['required', 'exists:users,id'],
            'name'          => ['required', 'min:4'],
            'email'         => 'required|email|max:191|unique:users,email,' . $this->input('id'),
            'date_of_birth'           => ['required', 'date', 'before:today'],
            'gender'        => 'required|' . Rule::in(array_column(Gender::cases(), 'value')),
            'phone'         => 'required|regex:/^(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})$/|unique:users,phone,' . $this->input('id'),
            'role_id'       => ['required', 'exists:roles,id'],
            'password'      => ['nullable', 'string', 'min:8'],
            'nid_number'    => ['nullable', 'numeric', 'digits_between:4,20'],
            'status'        => ['required', 'boolean'],
            'nid'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5098',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5098',
        ];
    }
}
