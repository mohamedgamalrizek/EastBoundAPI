<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Validation\Rule;
use App\Repositories\Accounting\AccountingRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code'    => ['required', 'string', 'max:50', 'unique:accounts,code'],
            'name'    => ['required', 'string', 'max:150'],
            'type'    => ['required', Rule::in(AccountingRepository::TYPES)],
            // Only the starting figure is entered; the live balance is
            // derived from the journal.
            'opening_balance' => ['required', 'numeric'],
            'cash_type'       => ['nullable', Rule::in(AccountingRepository::CASH_TYPES)],
        ];
    }
}
