<?php

namespace App\Http\Requests\AgentWithdrawal;

use App\Models\AgentWithdrawal;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The agent's own withdrawal request, from the portal or the mobile app. The
 * amount is checked against the available balance in the repository, where the
 * wallet and any pending requests are both known.
 */
class RequestWithdrawalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'method'          => ['required', Rule::in(AgentWithdrawal::METHODS)],
            'account_details' => ['required', 'string', 'max:190'],
            'note'            => ['nullable', 'string', 'max:190'],
        ];
    }
}
