<?php

namespace App\Http\Requests\Portal;

use Illuminate\Validation\Rule;
use App\Repositories\CustomerPortal\CustomerPortalRepository;
use Illuminate\Foundation\Http\FormRequest;

class PortalTravelerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // id is only present on update; ownership is enforced in the repository.
            'id'          => ['sometimes', 'required', 'integer'],
            'name'        => ['required', 'string', 'max:100'],
            'relation'    => ['required', Rule::in(CustomerPortalRepository::TRAVELER_RELATIONS)],
            'passport_no' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'dob'         => ['nullable', 'date', 'before:today'],
            'status'      => ['required', Rule::in(CustomerPortalRepository::TRAVELER_STATUSES)],
        ];
    }
}
