<?php

namespace App\Repositories\Receipt;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Receipt;
use App\Repositories\BaseRepository;
use App\Repositories\Receipt\ReceiptInterface;

class ReceiptRepository extends BaseRepository implements ReceiptInterface
{
    /** Allowed option sets — mirror the receipts migration. */
    public const METHODS = ['Cash', 'Bank', 'Card', 'bKash', 'Nagad', 'Wallet'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['invoice', 'customer'];

    public function __construct(Receipt $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'invoice_id'    => $request->invoice_id,
            'customer_id'   => $request->customer_id,
            'receipt_no'    => $request->receipt_no,
            'customer_name' => $request->customer_name,
            'received_on'   => $request->received_on,
            'amount'        => $request->amount,
            'method'        => $request->method,
            'reference'     => $request->reference,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (isset($filters['method'])) {
            $query->where('method', $filters['method']);
        }
    }

    public function formData(): array
    {
        return [
            // Amount + paid so the form can show what each invoice still owes.
            'invoices'  => Invoice::latest()->get(['id', 'invoice_no', 'customer_id', 'customer_name', 'amount', 'paid_amount']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'methods'   => self::METHODS,
        ];
    }
}
