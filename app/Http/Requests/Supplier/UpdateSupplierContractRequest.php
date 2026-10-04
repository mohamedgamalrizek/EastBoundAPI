<?php

namespace App\Http\Requests\Supplier;

use App\Repositories\Supplier\SupplierRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierContractRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'              => ['required', 'exists:supplier_contracts,id'],
            'supplier_id'     => ['required', 'exists:suppliers,id'],
            'contract_no'     => ['required', 'string', 'max:60', Rule::unique('supplier_contracts', 'contract_no')->ignore($this->input('id'))],
            'title'           => ['required', 'string', 'max:150'],
            'rate_type'       => ['required', Rule::in(SupplierRepository::CONTRACT_RATE_TYPES)],
            'value'           => ['nullable', 'numeric', 'min:0'],
            // Only collected for a commission deal; required when that is picked.
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100', Rule::requiredIf(fn () => $this->input('rate_type') === 'Commission')],
            'credit_days'     => ['nullable', 'integer', 'min:0', 'max:365'],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'terms'           => ['nullable', 'string', 'max:2000'],
            'status'          => ['required', Rule::in(SupplierRepository::CONTRACT_STATUSES)],
        ];
    }
}
