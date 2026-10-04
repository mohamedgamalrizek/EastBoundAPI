<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar'  => ['nullable', 'image', 'max:2048'],
            'tier'    => ['required', 'in:Silver,Gold,Platinum'],
            'status'  => ['required', 'in:active,inactive'],
            'notes'   => ['nullable', 'string'],
        ];
    }
}
