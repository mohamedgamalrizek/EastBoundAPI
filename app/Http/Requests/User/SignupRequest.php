<?php

namespace App\Http\Requests\User;

use App\Services\Contact\PhoneNumber;

use App\Enums\Gender;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    /**
     * Canonicalise the phone before any rule sees it.
     *
     * `unique:users,phone` is a string comparison, so validating the raw input
     * would let one person register as `01711000444` and `+8801711000444`
     * separately — and then only be able to log in with whichever spelling
     * they happened to use.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => PhoneNumber::e164($this->input('phone'), $this->input('dial_code')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|min:3|max:50',
            'email'             => 'required|email|unique:users,email',
            'phone'             => 'required|regex:/^(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})$/|unique:users,phone',
            'gender'            => 'required|' . Rule::in(array_column(Gender::cases(), 'value')),
            'date_of_birth'               => 'required|date|before:10 years ago|after:100 years ago',
            'password'          => 'required|string|min:6|max:32',
            'confirm_password'  => 'required|same:password',
            'ref'               => 'nullable|string|exists:customers,referral_code',
        ];
    }

    public function attributes()
    {
        return [
            'date_of_birth'       => 'Date of Birth',
            'email'     => 'E-mail address',
            'phone'     => 'Phone number',
        ];
    }


    public function messages()
    {
        return [
            'dob.required' => ___("alert.Date of Birth is required."),
        ];
    }
}
