<?php

namespace App\Repositories\Supplier;

use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\SupplierTransaction;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;
use App\Repositories\Supplier\SupplierInterface;

class SupplierRepository extends BaseRepository implements SupplierInterface
{
    /** Allowed option sets — mirror the suppliers migration. */
    public const TYPES    = ['Airline', 'Hotel', 'Transport', 'Visa'];
    public const STATUSES = ['active', 'inactive'];

    public const CONTRACT_RATE_TYPES = ['Fixed', 'Commission', 'Credit', 'Net rate'];
    public const CONTRACT_STATUSES   = ['Draft', 'Active', 'Expired', 'Terminated'];
    public const TXN_TYPES           = ['Bill', 'Payment', 'Credit Note', 'Adjustment'];

    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'name'           => $request->name,
            'type'           => $request->type,
            'contact_person' => $request->contact_person,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'balance'        => $request->balance,
            'status'         => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'types'    => self::TYPES,
            'statuses' => self::STATUSES,
        ];
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function airlines()
    {
        return ['suppliers' => Supplier::where('type', 'Airline')->latest()->get()];
    }

    public function hotels()
    {
        return ['suppliers' => Supplier::where('type', 'Hotel')->latest()->get()];
    }

    public function transport()
    {
        return ['suppliers' => Supplier::where('type', 'Transport')->latest()->get()];
    }

    public function visa()
    {
        return ['suppliers' => Supplier::where('type', 'Visa')->latest()->get()];
    }

    public function contracts()
    {
        return [
            'contracts' => SupplierContract::with('supplier')
                ->orderByDesc('start_date')
                ->orderBy('contract_no')
                ->get(),
        ];
    }

    public function contractFormData(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name', 'type']),
            'rateTypes' => self::CONTRACT_RATE_TYPES,
            'statuses'  => self::CONTRACT_STATUSES,
        ];
    }

    public function findContract($id)
    {
        return SupplierContract::with('supplier')->findOrFail($id);
    }

    public function storeContract($request)
    {
        try {
            SupplierContract::create($this->contractData($request));

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function updateContract($request)
    {
        try {
            SupplierContract::findOrFail($request->id)->update($this->contractData($request));

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function deleteContract($id)
    {
        try {
            SupplierContract::findOrFail($id)->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    protected function contractData($request): array
    {
        return [
            'supplier_id'     => $request->supplier_id,
            'contract_no'     => $request->contract_no,
            'title'           => $request->title,
            'rate_type'       => $request->rate_type,
            'value'           => $request->value ?: 0,
            // Only meaningful on a commission deal; blank it otherwise so an
            // edited contract does not keep a stale percentage.
            'commission_rate' => $request->rate_type === 'Commission' ? $request->commission_rate : null,
            'credit_days'     => $request->credit_days ?: null,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date ?: null,
            'terms'           => $request->terms,
            'status'          => $request->status,
        ];
    }

    /**
     * A real statement: every entry for the selected supplier with its running
     * balance, plus the outstanding total across all of them.
     */
    public function ledger(array $filters = [])
    {
        $supplierId = $filters['supplier_id'] ?? null;

        $entries = SupplierTransaction::with(['supplier', 'contract'])
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when(filled($filters['from'] ?? null), fn ($q) => $q->whereDate('txn_date', '>=', $filters['from']))
            ->when(filled($filters['to'] ?? null), fn ($q) => $q->whereDate('txn_date', '<=', $filters['to']))
            ->orderBy('txn_date')
            ->orderBy('id')
            ->get();

        return [
            'entries'      => $entries,
            'suppliers'    => Supplier::orderBy('name')->get(['id', 'name', 'type', 'balance']),
            'selected'     => $supplierId ? Supplier::find($supplierId) : null,
            'txnTypes'     => self::TXN_TYPES,
            'contracts'    => $supplierId
                ? SupplierContract::where('supplier_id', $supplierId)->orderBy('contract_no')->get()
                : collect(),
            'totalBilled'  => (float) $entries->sum('credit'),
            'totalPaid'    => (float) $entries->sum('debit'),
            'totalBalance' => (float) Supplier::sum('balance'),
        ];
    }

    /**
     * Record a bill or a payment and re-run the running balance.
     *
     * Everything after the new entry's date is recalculated, so back-dating an
     * invoice cannot leave the statement inconsistent.
     */
    public function storeTransaction($request)
    {
        try {
            DB::transaction(function () use ($request) {
                $isCredit = in_array($request->type, SupplierTransaction::CREDIT_TYPES, true);
                $amount   = (float) $request->amount;

                SupplierTransaction::create([
                    'supplier_id'          => $request->supplier_id,
                    'supplier_contract_id' => $request->supplier_contract_id ?: null,
                    'txn_date'             => $request->txn_date,
                    'type'                 => $request->type,
                    // Which account a payment left from; ignored on bills.
                    'method'               => $request->method,
                    'reference'            => $request->reference,
                    'description'          => $request->description,
                    'credit'               => $isCredit ? $amount : 0,
                    'debit'                => $isCredit ? 0 : $amount,
                    'balance_after'        => 0,
                ]);

                $this->rebuildRunningBalance($request->supplier_id);
            });

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function deleteTransaction($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $entry = SupplierTransaction::findOrFail($id);
                $supplierId = $entry->supplier_id;
                $entry->delete();

                $this->rebuildRunningBalance($supplierId);
            });

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /**
     * Recompute balance_after down the statement, then the supplier total.
     * Delegated so the ledger screen, the seeders and `accounting:rebuild`
     * cannot drift into three slightly different rules.
     */
    protected function rebuildRunningBalance($supplierId): void
    {
        app(\App\Services\Accounting\SupplierAccountingService::class)
            ->rebuildStatement((int) $supplierId);
    }

    public function reports()
    {
        return [
            'byType'        => Supplier::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
            'balanceByType' => Supplier::selectRaw('type, sum(balance) as total')->groupBy('type')->pluck('total', 'type'),
            'totalCount'    => Supplier::count(),
            'totalBalance'  => Supplier::sum('balance'),
            'activeCount'   => Supplier::where('status', 'active')->count(),
            // Contract and settlement figures now come from real records.
            'contractCount' => SupplierContract::count(),
            'activeContracts' => SupplierContract::where('status', 'Active')->count(),
            'expiringSoon'  => SupplierContract::where('status', 'Active')
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->count(),
            'billedThisYear' => (float) SupplierTransaction::whereYear('txn_date', now()->year)->sum('credit'),
            'paidThisYear'   => (float) SupplierTransaction::whereYear('txn_date', now()->year)->sum('debit'),
        ];
    }
}
