<?php

namespace App\Http\Requests\JobOpening;

use Illuminate\Validation\Rule;
use App\Repositories\JobOpening\JobOpeningRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobOpeningRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'department'      => ['nullable', 'string', 'max:120'],
            'location'        => ['nullable', 'string', 'max:180'],
            'employment_type' => ['required', Rule::in(JobOpeningRepository::EMPLOYMENT_TYPES)],
            'description'     => ['nullable', 'string', 'max:5000'],
            'closing_date'    => ['nullable', 'date'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'status'          => ['required', Rule::in(JobOpeningRepository::STATUSES)],
        ];
    }
}
