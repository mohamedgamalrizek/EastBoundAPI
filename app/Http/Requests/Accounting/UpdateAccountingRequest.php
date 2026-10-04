<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Validation\Rule;
use App\Repositories\Accounting\AccountingRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'      => ['required', 'exists:accounts,id'],
            'code'    => ['required', 'string', 'max:50', Rule::unique('accounts', 'code')->ignore($this->input('id'))],
            'name'    => ['required', 'string', 'max:150'],
            'type'    => ['required', Rule::in(AccountingRepository::TYPES)],
            // Only the starting figure is entered; the live balance is
            // derived from the journal.
            'opening_balance' => ['required', 'numeric'],
            'cash_type'       => ['nullable', Rule::in(AccountingRepository::CASH_TYPES)],
        ];
    }
}
