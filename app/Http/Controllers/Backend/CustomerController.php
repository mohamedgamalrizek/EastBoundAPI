<?php

namespace App\Http\Controllers\Backend;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\Customer\CustomerInterface;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;

class CustomerController extends Controller
{
    protected $repo;

    public function __construct(CustomerInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $customers = $this->repo->all();
        $analytics = $this->analytics();

        return view('backend.customer.index', compact('customers', 'analytics'));
    }

    /** KPI tiles + charts for the customer list page (over all customers). */
    private function analytics(): array
    {
        $byTier   = Customer::selectRaw('tier, COUNT(*) c')->groupBy('tier')->get();
        $active   = (int) Customer::where('status', 'active')->count();

        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));
        $added  = Customer::selectRaw("DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c")
            ->where('created_at', '>=', $months->first())
            ->groupBy('ym')->pluck('c', 'ym');

        return [
            'stats' => [
                'Total Customers' => $byTier->sum('c'),
                'Active'          => $active,
                'Platinum'        => (int) $byTier->firstWhere('tier', 'Platinum')?->c,
                'Gold'            => (int) $byTier->firstWhere('tier', 'Gold')?->c,
            ],
            'donut' => [
                'labels' => $byTier->pluck('tier')->all(),
                'series' => $byTier->pluck('c')->map(fn ($c) => (int) $c)->all(),
            ],
            'trend' => [
                'type'   => 'bar',
                'labels' => $months->map(fn ($m) => $m->format('M'))->all(),
                'series' => [
                    ['name' => 'New Customers', 'data' => $months->map(fn ($m) => (int) ($added[$m->format('Y-m')] ?? 0))->all()],
                ],
            ],
        ];
    }

    public function show($id, \App\Services\Accounting\CustomerWalletService $wallet)
    {
        $customer = $this->repo->get($id);

        return view('backend.customer.show', [
            'customer'           => $customer,
            'walletBalance'      => $customer ? $wallet->balance((int) $customer->id) : 0,
            'walletTransactions' => $customer
                ? $customer->walletTransactions()->orderByDesc('txn_date')->orderByDesc('id')->get()
                : collect(),
        ]);
    }

    /**
     * Top up or adjust a customer's wallet from the back office.
     *
     * This is real money moving: a top-up is booked Dr Cash/Bank, Cr Customer
     * Wallet, because the agency now holds it on the customer's behalf.
     */
    public function walletAdjust(Request $request, $id, \App\Services\Accounting\CustomerWalletService $wallet)
    {
        $validated = $request->validate([
            'type'        => ['required', 'in:credit,debit'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'method'      => ['required', 'in:Cash,Bank,Card,bKash,Nagad'],
            'description' => ['nullable', 'string', 'max:190'],
        ]);

        $customer = $this->repo->get($id);

        if (! $customer) {
            return back()->with('danger', ___('alert.something_went_wrong'));
        }

        // Balance check and debit run under one lock: two staff debiting the
        // same wallet at once (or a debit racing a booking paid from it)
        // cannot both pass the check against money only one of them can have.
        $shortBy = DB::transaction(function () use ($wallet, $customer, $validated) {
            $wallet->lockCustomer((int) $customer->id);
            $balance = $wallet->balance((int) $customer->id);

            if ($validated['type'] === 'debit' && $balance + 0.009 < $validated['amount']) {
                return $balance;
            }

            $wallet->adjust((int) $customer->id, $validated['type'], (float) $validated['amount'],
                $validated['description'] ?: ($validated['type'] === 'credit' ? 'Wallet top-up' : 'Wallet adjustment'),
                $validated['method']);

            return null;
        });

        if ($shortBy !== null) {
            return back()->with('danger', 'Wallet balance is only ' . currency_symbol()
                . number_format($shortBy, 2) . '.');
        }

        return back()->with('success', 'Wallet updated.');
    }

    public function create()
    {
        return view('backend.customer.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $result = $this->repo->store($request);

        if ($result['status']) {
            return redirect()->route('customer.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function edit($id)
    {
        $customer = $this->repo->get($id);

        return view('backend.customer.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, $id)
    {
        $result = $this->repo->update($request, $id);

        if ($result['status']) {
            return redirect()->route('customer.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }
}
