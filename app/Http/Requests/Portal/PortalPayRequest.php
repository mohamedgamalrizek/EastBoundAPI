<?php

namespace App\Http\Requests\Portal;

use App\Repositories\CustomerPortal\CustomerPortalRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PortalPayRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'     => ['required', 'integer'],
            'method' => ['required', Rule::in(CustomerPortalRepository::PAY_METHODS)],
        ];
    }
}
