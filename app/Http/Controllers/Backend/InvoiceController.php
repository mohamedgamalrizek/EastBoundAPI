<?php

namespace App\Http\Controllers\Backend;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Repositories\Invoice\InvoiceInterface;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;

class InvoiceController extends BaseCrudController
{
    protected string $viewPath      = 'backend.accounting.invoice';
    protected string $redirectRoute = 'acc.invoice.index';

    public function __construct(InvoiceInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        return view("{$this->viewPath}.index", [
            'items'     => $this->repo->all($request->query()),
            'analytics' => $this->analytics(),
        ]);
    }

    /** KPI tiles + charts for the invoice list page (over all invoices). */
    private function analytics(): array
    {
        $byStatus = Invoice::selectRaw('status, COUNT(*) c, SUM(amount) total')
            ->groupBy('status')->get();

        $outstanding = (float) $byStatus->whereIn('status', ['unpaid', 'overdue'])->sum('total');

        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));
        $billed = Invoice::selectRaw("DATE_FORMAT(created_at, '%Y-%m') ym, SUM(amount) total")
            ->where('created_at', '>=', $months->first())
            ->groupBy('ym')->pluck('total', 'ym');

        return [
            'stats' => [
                'Total Invoiced' => currency_symbol() . number_format((float) $byStatus->sum('total')),
                'Paid'           => currency_symbol() . number_format((float) $byStatus->firstWhere('status', 'paid')?->total),
                'Outstanding'    => currency_symbol() . number_format($outstanding),
                'Invoices'       => $byStatus->sum('c'),
            ],
            'donut' => [
                'labels' => $byStatus->pluck('status')->map(fn ($s) => ucfirst($s))->all(),
                'series' => $byStatus->pluck('c')->map(fn ($c) => (int) $c)->all(),
            ],
            'trend' => [
                'type'   => 'area',
                'labels' => $months->map(fn ($m) => $m->format('M'))->all(),
                'series' => [
                    ['name' => 'Billed', 'data' => $months->map(fn ($m) => (float) ($billed[$m->format('Y-m')] ?? 0))->all()],
                ],
            ],
        ];
    }

    public function store(StoreInvoiceRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateInvoiceRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /**
     * Give money back against an invoice. The invoice and its sale stay on the
     * books; the refund is posted against them (Dr Refunds, Cr cash/bank/
     * wallet) and the invoice's status is re-derived.
     */
    public function refund(Request $request, $id, \App\Services\Accounting\BillingService $billing)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:Cash,Bank,Wallet'],
            'reason' => ['nullable', 'string', 'max:190'],
        ]);

        $refundable = $billing->refundableAmount($invoice);

        if ($validated['amount'] > $refundable + 0.009) {
            return back()->with('danger',
                'Only ' . currency_symbol() . number_format($refundable, 2) . ' can be refunded on ' . $invoice->invoice_no
                . ' — that is what has been received and not yet given back.');
        }

        if ($validated['method'] === 'Wallet' && ! $invoice->customer_id) {
            return back()->with('danger', 'This invoice has no saved customer, so it cannot be refunded to a wallet.');
        }

        $refund = $billing->refund($invoice, (float) $validated['amount'], $validated['method'], $validated['reason'] ?? null);

        return redirect()->route($this->redirectRoute)
            ->with('success', 'Refund ' . $refund->reference . ' recorded and posted.');
    }
}
