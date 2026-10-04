<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Validation\Rule;
use App\Repositories\Invoice\InvoiceRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'            => ['required', 'exists:invoices,id'],
            'customer_id'   => ['nullable', 'exists:customers,id'],
            'booking_id'    => ['nullable', 'exists:bookings,id'],
            'invoice_no'    => ['required', 'string', 'max:50', Rule::unique('invoices', 'invoice_no')->ignore($this->input('id'))],
            'customer_name' => ['required', 'string', 'max:100'],
            'issue_date'    => ['required', 'date'],
            'due_date'      => ['nullable', 'date', 'after_or_equal:issue_date'],
            'amount'        => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
