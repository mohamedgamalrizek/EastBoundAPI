<?php

namespace App\Services\Dmc;

use App\Models\Account;
use App\Models\Dmc\PurchaseRequest;
use App\Models\Dmc\Rfq;
use App\Models\Dmc\VendorBill;
use App\Models\Dmc\VendorOrder;
use App\Models\Dmc\VendorPayment;
use App\Models\Supplier;
use App\Models\SupplierTransaction;
use App\Models\User;
use App\Services\Accounting\SupplierAccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcurementWorkflow
{
    public function transition(Rfq $rfq, string $action, ?User $actor = null, ?string $note = null): Rfq|VendorOrder
    {
        return DB::transaction(function () use ($rfq, $action, $actor, $note) {
            $request = PurchaseRequest::lockForUpdate()->findOrFail($rfq->purchase_request_id);
            $rfq = Rfq::lockForUpdate()->findOrFail($rfq->id);
            if ($action === 'award' && $rfq->order) {
                return $rfq->order;
            }
            $this->ensure($request->status !== 'Ordered', 'A vendor order already exists for this request.');
            if ($action === 'send') {
                $this->ensure($rfq->status === 'Draft', 'Only draft RFQs can be marked sent.');
                $rfq->update(['status' => 'Sent']);
                $request->update(['status' => 'RFQ']);
            } elseif ($action === 'respond') {
                $this->ensure($rfq->status === 'Sent' && (float) $rfq->quoted_amount > 0, 'Record a positive vendor quote on a sent RFQ first.');
                $rfq->update(['status' => 'Responded']);
            } elseif ($action === 'award') {
                $this->ensure($rfq->status === 'Responded' && $rfq->valid_until->endOfDay()->gte(now()), 'A valid vendor response is required.');
                $overBudget = (float) $rfq->quoted_amount > (float) $request->estimated_cost + 0.001;
                $this->ensure(! $overBudget || ($actor?->can_access('dmc_sales_approve_gm') && filled($note)), 'A vendor quote above the approved budget needs General Manager authorization and a reason.');
                $order = VendorOrder::create([
                    'purchase_request_id' => $request->id, 'rfq_id' => $rfq->id,
                    'supplier_id' => $rfq->supplier_id, 'trip_id' => $request->trip_id,
                    'amount' => $rfq->quoted_amount, 'required_on' => $request->required_on,
                    'variance_approved_by' => $overBudget ? $actor->id : null, 'variance_reason' => $overBudget ? $note : null,
                ]);
                $request->update(['status' => 'Ordered']);
                $request->tripItem->update(['supplier_id' => $rfq->supplier_id]);
                $rfq->update(['status' => 'Accepted']);
                Rfq::where('purchase_request_id', $request->id)->where('id', '!=', $rfq->id)->get()
                    ->each(fn ($other) => $other->update(['status' => 'Rejected']));

                return $order;
            } else {
                abort(422, 'Unsupported purchasing action.');
            }

            return $rfq->fresh();
        }, 3);
    }

    public function bill(array $data): VendorBill
    {
        return DB::transaction(function () use ($data) {
            $order = VendorOrder::lockForUpdate()->findOrFail($data['vendor_order_id']);
            Supplier::lockForUpdate()->findOrFail($order->supplier_id);
            $existing = VendorBill::where('supplier_id', $order->supplier_id)->where('reference', $data['reference'])->first();
            if ($existing) {
                $this->ensure($existing->vendor_order_id === $order->id && abs((float) $existing->amount - $data['amount']) < 0.001
                    && $existing->issued_on->toDateString() === $data['issued_on'] && $existing->due_on->toDateString() === $data['due_on'], 'This vendor invoice reference is already used by a different bill.');

                return $existing;
            }
            $this->ensure(in_array($order->status, ['Issued', 'Received']), 'The vendor order is not billable.');
            $remaining = round((float) $order->amount - (float) $order->bills()->sum('amount'), 2);
            $this->ensure((float) $data['amount'] <= $remaining + 0.001, 'This bill exceeds the unbilled vendor order amount.');
            $this->accountsReady();
            $bill = VendorBill::create($data + ['supplier_id' => $order->supplier_id]);
            $entry = $this->entry($order->supplier_id, 'Bill', $data['amount'], $data['issued_on'], 'DMC-BILL-'.$bill->id, null);
            $bill->update(['supplier_transaction_id' => $entry->id]);

            return $bill;
        }, 3);
    }

    public function pay(array $data): VendorPayment
    {
        return DB::transaction(function () use ($data) {
            $bill = VendorBill::lockForUpdate()->findOrFail($data['vendor_bill_id']);
            $existing = VendorPayment::where('idempotency_key', $data['idempotency_key'])->first()
                ?? VendorPayment::where('vendor_bill_id', $bill->id)->where('reference', $data['reference'])->first();
            if ($existing) {
                $this->ensure($existing->vendor_bill_id === $bill->id && abs((float) $existing->amount - $data['amount']) < 0.001
                    && $existing->reference === $data['reference'] && $existing->method === $data['method']
                    && $existing->paid_on->toDateString() === $data['paid_on'], 'This payment identifier belongs to a different payment.');

                return $existing;
            }
            $remaining = round((float) $bill->amount - (float) $bill->payments()->sum('amount'), 2);
            $this->ensure((float) $data['amount'] <= $remaining + 0.001, 'This payment exceeds the outstanding vendor bill.');
            $this->accountsReady();
            $payment = VendorPayment::create($data);
            $entry = $this->entry($bill->supplier_id, 'Payment', $data['amount'], $data['paid_on'], 'DMC-PAY-'.$payment->id, $data['method']);
            $payment->update(['supplier_transaction_id' => $entry->id]);

            return $payment;
        }, 3);
    }

    private function accountsReady(): void
    {
        foreach (['supplier_cost', 'payable', 'cash', 'bank'] as $key) {
            $this->ensure(Account::system($key) !== null, 'Missing accounting system account: '.$key);
        }
    }

    private function entry(int $supplierId, string $type, float $amount, string $date, string $reference, ?string $method): SupplierTransaction
    {
        Supplier::lockForUpdate()->findOrFail($supplierId);
        $entry = SupplierTransaction::create([
            'supplier_id' => $supplierId, 'txn_date' => $date, 'type' => $type, 'method' => $method,
            'reference' => $reference, 'description' => 'DMC procurement',
            'credit' => $type === 'Bill' ? $amount : 0, 'debit' => $type === 'Payment' ? $amount : 0,
            'balance_after' => 0,
        ]);
        $this->ensure($entry->journalEntries()->exists(), 'The accounting entry could not be posted.');
        app(SupplierAccountingService::class)->rebuildStatement($supplierId);

        return $entry;
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['workflow' => $message]);
        }
    }
}
