<?php

namespace App\Http\Requests\AgentCommission;

use Illuminate\Validation\Rule;
use App\Repositories\AgentCommission\AgentCommissionRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAgentCommissionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'            => ['required', 'exists:agent_commissions,id'],
            'agent_id'      => ['nullable', 'exists:users,id'],
            'customer_id'   => ['nullable', 'exists:customers,id'],
            'reference'     => ['required', 'string', 'max:50', Rule::unique('agent_commissions', 'reference')->ignore($this->input('id'))],
            'booking_ref'   => ['required', 'string', 'max:50'],
            'customer_name' => ['required', 'string', 'max:100'],
            'amount'        => ['required', 'numeric', 'min:0'],
            'rate'          => ['required', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(AgentCommissionRepository::STATUSES)],
            'earned_on'     => ['nullable', 'date'],
        ];
    }
}
