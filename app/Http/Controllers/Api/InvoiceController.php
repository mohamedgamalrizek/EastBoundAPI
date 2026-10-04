<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;

/**
 * Billing for the Customer app: invoices the agency issued and the receipts
 * (payments) recorded against them. Read-only — paying happens through
 * /bookings/{id}/pay or the wallet.
 */
class InvoiceController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer;

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $query = Invoice::where('customer_id', $customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rows = $query->latest('issue_date')->get();

        $invoices = $rows->map(fn (Invoice $i) => $this->invoiceInfo($i));

        // Outstanding is what is still OWED — amount less what was received —
        // summed over every invoice that owes anything. Summing the face
        // amount of unpaid/overdue rows both missed partial invoices entirely
        // and over-counted part-paid overdue ones.
        $owing = $rows->filter(fn (Invoice $i) => in_array($i->status, ['unpaid', 'partial', 'overdue'], true));

        return $this->responseWithSuccess('Invoices fetched.', [
            'invoices' => $invoices,
            'summary'  => [
                'total_invoiced' => round((float) $rows->sum('amount'), 2),
                'outstanding'    => round((float) $owing->sum(fn (Invoice $i) => max(0, $i->dueAmount())), 2),
                'paid_count'     => $rows->where('status', 'paid')->count(),
                'unpaid_count'   => $owing->count(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $invoice = Invoice::with('receipts')->where('customer_id', $customer->id)->find($id);
        if (! $invoice) {
            return $this->responseWithError('Invoice not found.', [], 404);
        }

        return $this->responseWithSuccess('Invoice fetched.', [
            'invoice' => array_merge($this->invoiceInfo($invoice), [
                'receipts' => $invoice->receipts->map(fn (Receipt $r) => $this->receiptInfo($r)),
            ]),
        ]);
    }

    /** Payments (receipts) recorded against this customer. */
    public function payments(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $receipts = Receipt::where('customer_id', $customer->id)
            ->latest('received_on')
            ->get()
            ->map(fn (Receipt $r) => $this->receiptInfo($r));

        return $this->responseWithSuccess('Payments fetched.', [
            'payments'   => $receipts,
            'total_paid' => round((float) $receipts->sum('amount'), 2),
        ]);
    }

    private function invoiceInfo(Invoice $i): array
    {
        return [
            'id'              => $i->id,
            'invoice_no'      => $i->invoice_no,
            'customer_name'   => $i->customer_name,
            'booking_id'      => $i->booking_id,
            'issue_date'      => optional($i->issue_date)->toDateString(),
            'due_date'        => optional($i->due_date)->toDateString(),
            'amount'          => (float) $i->amount,
            // What a partial invoice still owes — the web portal always showed
            // this; now the app can too.
            'paid_amount'     => (float) $i->paid_amount,
            'refunded_amount' => (float) $i->refunded_amount,
            'due_amount'      => round(max(0, $i->dueAmount()), 2),
            'status'          => $i->status,
        ];
    }

    private function receiptInfo(Receipt $r): array
    {
        return [
            'id'          => $r->id,
            'receipt_no'  => $r->receipt_no,
            'invoice_id'  => $r->invoice_id,
            'received_on' => optional($r->received_on)->toDateString(),
            'amount'      => (float) $r->amount,
            'method'      => $r->method,
            'reference'   => $r->reference,
        ];
    }
}
