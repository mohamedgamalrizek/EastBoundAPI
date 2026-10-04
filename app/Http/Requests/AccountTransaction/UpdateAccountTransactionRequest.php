<?php

namespace App\Http\Requests\AccountTransaction;

use Illuminate\Validation\Rule;
use App\Repositories\AccountTransaction\AccountTransactionRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountTransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'           => ['required', 'exists:account_transactions,id'],
            'account_id'        => ['required', 'exists:accounts,id'],
            // The second leg. Without it an entry cannot be posted as a
            // debit and a credit, and the trial balance would never agree.
            'contra_account_id' => ['required', 'exists:accounts,id', 'different:account_id'],
            'txn_date'          => ['required', 'date'],
            'account_name'      => ['nullable', 'string', 'max:150'],
            'type'              => ['required', Rule::in(AccountTransactionRepository::TYPES)],
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'reference'    => ['nullable', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:255'],
        ];
    }
}
