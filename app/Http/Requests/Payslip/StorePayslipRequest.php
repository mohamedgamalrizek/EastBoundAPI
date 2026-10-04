<?php

namespace App\Http\Requests\Payslip;

use Illuminate\Validation\Rule;
use App\Repositories\Payslip\PayslipRepository;
use Illuminate\Foundation\Http\FormRequest;

class StorePayslipRequest extends FormRequest
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
            'month'      => ['required', 'string', 'max:20'],
            'basic'      => ['required', 'numeric', 'min:0'],
            'allowances' => ['required', 'numeric', 'min:0'],
            'deductions' => ['required', 'numeric', 'min:0'],
            'net_pay'    => ['required', 'numeric', 'min:0'],
            'status'     => ['required', Rule::in(PayslipRepository::STATUSES)],
        ];
    }
}
