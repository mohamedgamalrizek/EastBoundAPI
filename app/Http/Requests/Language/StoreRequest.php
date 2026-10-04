<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'            =>   ['required', 'max:100'],
            'code'            =>   ['required', 'min:1', 'max:4', 'unique:languages'],
            'icon_class'      =>   ['required'],
            'text_direction'  =>   ['required', 'in:ltr,rtl'],
            'status'            => ['required', 'boolean'],
        ];
    }
}
