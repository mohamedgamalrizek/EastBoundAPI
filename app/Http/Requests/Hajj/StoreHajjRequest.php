<?php

namespace App\Http\Requests\Hajj;

use Illuminate\Validation\Rule;
use App\Repositories\Hajj\HajjRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreHajjRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'package_no'    => ['required', 'string', 'max:50', 'unique:hajj_packages,package_no'],
            'title'         => ['required', 'string', 'max:150'],
            'type'          => ['required', Rule::in(HajjRepository::TYPES)],
            'duration_days' => ['required', 'integer', 'min:1'],
            'price'         => ['required', 'numeric', 'min:0'],
            'seats'         => ['required', 'integer', 'min:0'],
            'status'        => ['required', Rule::in(HajjRepository::STATUSES)],
        ];
    }
}
