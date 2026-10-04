<?php

namespace App\Http\Requests\Traveler;

use Illuminate\Validation\Rule;
use App\Repositories\Traveler\TravelerRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTravelerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'          => ['required', 'exists:travelers,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'name'        => ['required', 'string', 'max:100'],
            'relation'    => ['required', Rule::in(TravelerRepository::RELATIONS)],
            'passport_no' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'dob'         => ['nullable', 'date'],
            'status'      => ['required', Rule::in(TravelerRepository::STATUSES)],
        ];
    }
}
