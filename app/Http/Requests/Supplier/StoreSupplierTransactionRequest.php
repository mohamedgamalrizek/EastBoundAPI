<?php

namespace App\Http\Requests\Supplier;

use App\Repositories\Supplier\SupplierRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierTransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'supplier_id'          => ['required', 'exists:suppliers,id'],
            'supplier_contract_id' => ['nullable', 'exists:supplier_contracts,id'],
            'txn_date'             => ['required', 'date'],
            'type'                 => ['required', Rule::in(SupplierRepository::TXN_TYPES)],
            // Which account a payment leaves from; ignored on other types.
            'method'               => ['nullable', Rule::in(\App\Models\SupplierTransaction::METHODS)],
            'reference'            => ['nullable', 'string', 'max:100'],
            'description'          => ['nullable', 'string', 'max:255'],
            // A zero-value entry would only pollute the statement.
            'amount'               => ['required', 'numeric', 'gt:0'],
        ];
    }
}
