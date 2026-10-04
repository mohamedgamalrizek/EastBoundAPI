<?php

namespace App\Http\Requests\AgentCommission;

use Illuminate\Validation\Rule;
use App\Repositories\AgentCommission\AgentCommissionRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgentCommissionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'agent_id'      => ['nullable', 'exists:users,id'],
            'customer_id'   => ['nullable', 'exists:customers,id'],
            'reference'     => ['required', 'string', 'max:50', 'unique:agent_commissions,reference'],
            'booking_ref'   => ['required', 'string', 'max:50'],
            'customer_name' => ['required', 'string', 'max:100'],
            'amount'        => ['required', 'numeric', 'min:0'],
            'rate'          => ['required', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(AgentCommissionRepository::STATUSES)],
            'earned_on'     => ['nullable', 'date'],
        ];
    }
}
