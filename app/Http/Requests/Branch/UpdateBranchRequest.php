<?php

namespace App\Http\Requests\Branch;

use Illuminate\Validation\Rule;
use App\Repositories\Branch\BranchRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:191'],
            'code' => ['required', 'string', 'max:191'],
            'manager_name' => ['nullable', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191'],
            'city' => ['nullable', 'string', 'max:191'],
            'address' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(BranchRepository::STATUS)],
        ];
    }
}
