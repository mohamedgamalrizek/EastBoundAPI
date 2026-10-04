<?php

namespace App\Http\Requests\Portal;

use Illuminate\Validation\Rule;
use App\Repositories\StaffPortal\StaffPortalRepository;
use Illuminate\Foundation\Http\FormRequest;

class PortalLeaveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'leave_type' => ['required', Rule::in(StaffPortalRepository::LEAVE_TYPES)],
            'from_date'  => ['required', 'date'],
            'to_date'    => ['required', 'date', 'after_or_equal:from_date'],
            'reason'     => ['nullable', 'string', 'max:500'],
        ];
    }
}
