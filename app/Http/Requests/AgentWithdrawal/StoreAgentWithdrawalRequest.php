<?php

namespace App\Http\Requests\AgentWithdrawal;

use App\Models\AgentWithdrawal;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgentWithdrawalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'agent_id'        => ['required', 'exists:users,id'],
            'reference'       => ['nullable', 'string', 'max:50', 'unique:agent_withdrawals,reference'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'method'          => ['required', Rule::in(AgentWithdrawal::METHODS)],
            'account_details' => ['nullable', 'string', 'max:190'],
            'status'          => ['nullable', Rule::in(AgentWithdrawal::STATUSES)],
            'requested_on'    => ['nullable', 'date'],
            'note'            => ['nullable', 'string', 'max:190'],
        ];
    }
}
