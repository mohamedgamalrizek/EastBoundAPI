<?php

namespace App\Http\Requests\Support;

use Illuminate\Validation\Rule;
use App\Repositories\Support\SupportRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSupportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'            => ['required', 'exists:support_tickets,id'],
            'customer_id'   => ['nullable', 'exists:customers,id'],
            'assigned_to'   => ['nullable', 'exists:users,id'],
            'ticket_no'     => ['required', 'string', 'max:50', Rule::unique('support_tickets', 'ticket_no')->ignore($this->input('id'))],
            'subject'       => ['required', 'string', 'max:150'],
            'customer_name' => ['required', 'string', 'max:100'],
            'priority'      => ['required', Rule::in(SupportRepository::PRIORITIES)],
            'department'    => ['nullable', 'string', 'max:100'],
            'status'        => ['required', Rule::in(SupportRepository::STATUSES)],
        ];
    }
}
