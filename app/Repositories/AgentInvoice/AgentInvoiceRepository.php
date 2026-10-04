<?php

namespace App\Repositories\AgentInvoice;

use App\Models\AgentInvoice;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\AgentInvoice\AgentInvoiceInterface;

class AgentInvoiceRepository extends BaseRepository implements AgentInvoiceInterface
{
    /** Allowed option sets — mirror the agent_invoices migration. */
    public const STATUSES = ['paid', 'unpaid', 'overdue'];

    /** How a settled invoice was paid. Wallet nets it off the agent's balance. */
    public const METHODS = ['Wallet', 'Cash', 'Bank', 'bKash', 'Nagad'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['agent'];

    public function __construct(AgentInvoice $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        $paid = $request->status === 'paid';

        return [
            'agent_id'      => $request->agent_id,
            'invoice_no'    => $request->invoice_no,
            'customer_name' => $request->customer_name,
            'amount'        => $request->amount,
            'issued_on'     => $request->issued_on,
            'due_on'        => $request->due_on,
            'status'        => $request->status,
            // Settlement details only mean something on a paid invoice; the
            // observer posts from these, so a non-paid row must carry none.
            'method'        => $paid ? ($request->method ?: 'Wallet') : null,
            'paid_on'       => $paid ? ($request->paid_on ?: now()->toDateString()) : null,
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
        if (isset($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'agents'   => User::orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
            'methods'  => self::METHODS,
        ];
    }
}
