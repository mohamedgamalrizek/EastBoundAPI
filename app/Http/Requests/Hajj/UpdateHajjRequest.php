<?php

namespace App\Http\Requests\Hajj;

use Illuminate\Validation\Rule;
use App\Repositories\Hajj\HajjRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHajjRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'            => ['required', 'exists:hajj_packages,id'],
            'package_no'    => ['required', 'string', 'max:50', Rule::unique('hajj_packages', 'package_no')->ignore($this->input('id'))],
            'title'         => ['required', 'string', 'max:150'],
            'type'          => ['required', Rule::in(HajjRepository::TYPES)],
            'duration_days' => ['required', 'integer', 'min:1'],
            'price'         => ['required', 'numeric', 'min:0'],
            'seats'         => ['required', 'integer', 'min:0'],
            'status'        => ['required', Rule::in(HajjRepository::STATUSES)],
        ];
    }
}
