<?php

namespace App\Repositories\Invoice;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Repositories\BaseRepository;
use App\Repositories\Invoice\InvoiceInterface;

class InvoiceRepository extends BaseRepository implements InvoiceInterface
{
    /**
     * Statuses are derived, never chosen: LedgerService::reconcileInvoice sets
     * them from the receipts recorded against the invoice. Listed here only so
     * the list page can filter by them.
     */
    public const STATUSES = Invoice::STATUSES;

    /** Relations eager-loaded on list / find. */
    protected array $with = ['customer', 'booking', 'receipts'];

    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'customer_id'   => $request->customer_id,
            'booking_id'    => $request->booking_id,
            'invoice_no'    => $request->invoice_no,
            'customer_name' => $request->customer_name,
            'issue_date'    => $request->issue_date,
            'due_date'      => $request->due_date,
            'amount'        => $request->amount,
            // No 'status' / 'paid_amount': both are re-derived from the
            // invoice's receipts as soon as it is saved.
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    /** An invoice that has been paid against keeps its receipts' history. */
    protected function guardDelete($model): ?string
    {
        if ($model->receipts()->exists()) {
            return 'This invoice has receipts against it — delete those first.';
        }

        return null;
    }

    public function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'bookings'  => Booking::latest()->get(['id', 'customer_id', 'customer_name', 'amount', 'travel_date']),
            'statuses'  => self::STATUSES,
        ];
    }
}
