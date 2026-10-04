<?php

namespace App\Http\Requests\Receipt;

use Illuminate\Validation\Rule;
use App\Repositories\Receipt\ReceiptRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReceiptRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'            => ['required', 'exists:receipts,id'],
            'invoice_id'    => ['required', 'exists:invoices,id'],
            'customer_id'   => ['nullable', 'exists:customers,id'],
            'receipt_no'    => ['required', 'string', 'max:50', Rule::unique('receipts', 'receipt_no')->ignore($this->input('id'))],
            'customer_name' => ['required', 'string', 'max:100'],
            'received_on'   => ['required', 'date'],
            'amount'        => ['required', 'numeric', 'min:0.01', function ($attribute, $value, $fail) {
                // A receipt cannot collect more than the invoice still owes,
                // otherwise the invoice would report as overpaid and the
                // receivable account would go negative.
                $invoice = \App\Models\Invoice::find($this->input('invoice_id'));

                if (! $invoice) {
                    return;
                }

                $alreadyPaid = (float) $invoice->receipts()
                    ->when($this->input('id'), fn ($q) => $q->whereKeyNot($this->input('id')))
                    ->sum('amount');

                $outstanding = round((float) $invoice->amount - $alreadyPaid, 2);

                if ((float) $value > $outstanding + 0.009) {
                    $fail('Amount exceeds the outstanding balance on invoice ' . $invoice->invoice_no
                        . ' (' . currency_symbol() . number_format($outstanding, 2) . ').');
                }
            }],
            'method'        => ['required', Rule::in(ReceiptRepository::METHODS)],
            'reference'     => ['nullable', 'string', 'max:100'],
        ];
    }
}
