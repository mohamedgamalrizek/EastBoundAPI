<?php

namespace App\Http\Controllers\Backend;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Repositories\Supplier\SupplierInterface;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Requests\Supplier\StoreSupplierContractRequest;
use App\Http\Requests\Supplier\UpdateSupplierContractRequest;
use App\Http\Requests\Supplier\StoreSupplierTransactionRequest;
use App\Http\Controllers\Backend\Concerns\BuildsListAnalytics;

class SupplierController extends BaseCrudController
{
    use BuildsListAnalytics;

    protected string $viewPath      = 'backend.supplier';
    protected string $redirectRoute = 'supplier.index';

    public function __construct(SupplierInterface $repo)
    {
        $this->repo = $repo;
    }

    protected function listAnalytics(Request $request): ?array
    {
        return [
            'stats' => [
                'Total Suppliers' => Supplier::count(),
                'Active'          => Supplier::where('status', 'active')->count(),
                'Total Balance'   => currency_symbol() . number_format((float) Supplier::sum('balance')),
                'Types'           => (int) Supplier::distinct('type')->count('type'),
            ],
            'donut' => $this->laGroup(Supplier::class, 'type'),
            'trend' => [
                'type'   => 'bar',
                'labels' => Supplier::selectRaw('type, SUM(balance) b')->groupBy('type')->orderByDesc('b')->pluck('type')->all(),
                'series' => [['name' => 'Balance', 'data' => Supplier::selectRaw('type, SUM(balance) b')->groupBy('type')->orderByDesc('b')->pluck('b')->map(fn ($v) => (float) $v)->all()]],
            ],
        ];
    }

    public function store(StoreSupplierRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateSupplierRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function airlines()
    {
        return view('backend.supplier.airlines', $this->repo->airlines());
    }

    public function hotels()
    {
        return view('backend.supplier.hotels', $this->repo->hotels());
    }

    public function transport()
    {
        return view('backend.supplier.transport', $this->repo->transport());
    }

    public function visa()
    {
        return view('backend.supplier.visa', $this->repo->visa());
    }

    /* ---- Contracts (real records) --------------------------------------- */

    public function contracts()
    {
        return view('backend.supplier.contracts', $this->repo->contracts());
    }

    public function contractCreate()
    {
        return view('backend.supplier.contract-create', $this->repo->contractFormData());
    }

    public function contractStore(StoreSupplierContractRequest $request)
    {
        return $this->supplierRedirect($this->repo->storeContract($request), 'sup.contracts');
    }

    public function contractEdit($id)
    {
        return view('backend.supplier.contract-edit', $this->repo->contractFormData() + [
            'item' => $this->repo->findContract($id),
        ]);
    }

    public function contractUpdate(UpdateSupplierContractRequest $request)
    {
        return $this->supplierRedirect($this->repo->updateContract($request), 'sup.contracts');
    }

    public function contractDelete($id)
    {
        $result = $this->repo->deleteContract($id);

        return response()->json($result, $result['status_code'] ?? 200);
    }

    /* ---- Ledger (real statement) ---------------------------------------- */

    public function ledger(Request $request)
    {
        return view('backend.supplier.ledger', $this->repo->ledger(
            $request->only(['supplier_id', 'from', 'to'])
        ));
    }

    public function ledgerStore(StoreSupplierTransactionRequest $request)
    {
        $result = $this->repo->storeTransaction($request);

        // Stay on the supplier whose statement was just posted to.
        return $result['status']
            ? redirect()->route('sup.ledger', ['supplier_id' => $request->supplier_id])->with('success', $result['message'])
            : back()->with('danger', $result['message'])->withInput();
    }

    public function ledgerDelete($id)
    {
        $result = $this->repo->deleteTransaction($id);

        return response()->json($result, $result['status_code'] ?? 200);
    }

    private function supplierRedirect(array $result, string $route)
    {
        return $result['status']
            ? redirect()->route($route)->with('success', $result['message'])
            : back()->with('danger', $result['message'])->withInput();
    }

    public function reports()
    {
        return view('backend.supplier.reports', $this->repo->reports());
    }
}
