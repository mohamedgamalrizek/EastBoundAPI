<?php

namespace App\Http\Requests\StaffAttendance;

use Illuminate\Validation\Rule;
use App\Repositories\StaffAttendance\StaffAttendanceRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'         => ['required', 'exists:staff_attendance,id'],
            'user_id'    => ['nullable', 'exists:users,id'],
            'staff_name' => ['required', 'string', 'max:100'],
            'date'       => ['required', 'date'],
            'check_in'   => ['nullable', 'string', 'max:20'],
            'check_out'  => ['nullable', 'string', 'max:20'],
            'hours'      => ['nullable', 'numeric', 'min:0'],
            'status'     => ['required', Rule::in(StaffAttendanceRepository::STATUSES)],
        ];
    }
}
