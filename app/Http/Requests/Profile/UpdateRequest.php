<?php

namespace App\Http\Requests\Profile;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'name'          => 'required|string|min:4|max:50',
            'date_of_birth' => 'required|date|before:today',
            'gender'        => 'required|' . Rule::in(array_column(Gender::cases(), 'value')),
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5098',
            'address'       => 'required|string|min:5|max:100',
            'about'         => 'nullable|string|min:5|max:200',
        ];
    }
}
