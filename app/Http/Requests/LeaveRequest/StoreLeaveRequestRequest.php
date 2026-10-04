<?php

namespace App\Http\Requests\LeaveRequest;

use Illuminate\Validation\Rule;
use App\Repositories\LeaveRequest\LeaveRequestRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id'    => ['nullable', 'exists:users,id'],
            'staff_name' => ['required', 'string', 'max:100'],
            'leave_type' => ['required', Rule::in(LeaveRequestRepository::LEAVE_TYPES)],
            'from_date'  => ['required', 'date'],
            'to_date'    => ['required', 'date', 'after_or_equal:from_date'],
            'days'       => ['required', 'integer', 'min:0'],
            'reason'     => ['nullable', 'string', 'max:500'],
            'status'     => ['required', Rule::in(LeaveRequestRepository::STATUSES)],
        ];
    }
}
