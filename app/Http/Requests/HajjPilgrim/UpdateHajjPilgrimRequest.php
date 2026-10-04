<?php

namespace App\Http\Requests\HajjPilgrim;

use Illuminate\Validation\Rule;
use App\Repositories\HajjPilgrim\HajjPilgrimRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHajjPilgrimRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'              => ['required', 'exists:hajj_pilgrims,id'],
            'hajj_package_id' => ['required', 'exists:hajj_packages,id'],
            'customer_id'     => ['nullable', 'exists:customers,id'],
            'pilgrim_no'      => ['nullable', 'string', 'max:50', Rule::unique('hajj_pilgrims', 'pilgrim_no')->ignore($this->input('id'))],
            'name'            => ['required', 'string', 'max:100'],
            'passport_no'     => ['required', 'string', 'max:50'],
            'group_name'      => ['nullable', 'string', 'max:100'],
            'document_status' => ['required', Rule::in(HajjPilgrimRepository::DOCUMENT_STATUSES)],
            'status'          => ['required', Rule::in(HajjPilgrimRepository::STATUSES)],
        ];
    }
}
