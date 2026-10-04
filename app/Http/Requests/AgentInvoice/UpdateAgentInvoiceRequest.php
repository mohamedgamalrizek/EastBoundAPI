<?php

namespace App\Http\Requests\AgentInvoice;

use Illuminate\Validation\Rule;
use App\Repositories\AgentInvoice\AgentInvoiceRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAgentInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'            => ['required', 'exists:agent_invoices,id'],
            'agent_id'      => ['nullable', 'exists:users,id'],
            'invoice_no'    => ['required', 'string', 'max:50', Rule::unique('agent_invoices', 'invoice_no')->ignore($this->input('id'))],
            'customer_name' => ['required', 'string', 'max:100'],
            'amount'        => ['required', 'numeric', 'min:0'],
            'issued_on'     => ['nullable', 'date'],
            'due_on'        => ['nullable', 'date'],
            'status'        => ['required', Rule::in(AgentInvoiceRepository::STATUSES)],
            'method'        => ['nullable', 'required_if:status,paid', Rule::in(AgentInvoiceRepository::METHODS)],
            'paid_on'       => ['nullable', 'date'],
        ];
    }
}
