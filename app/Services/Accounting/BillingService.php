<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\EventBooking;
use App\Models\FlightBooking;
use App\Models\HajjPilgrim;
use App\Models\HotelBooking;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Refund;
use App\Models\TransportBooking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Turns a booking into money the books can see, and money back out again.
 *
 *   booking confirmed/paid -> invoice raised for it (Dr AR, Cr Sales)
 *   booking paid           -> receipt for what is outstanding, in the method
 *                             the customer paid with (Dr Cash/Bank/Wallet,
 *                             Cr AR)
 *   refund                 -> Dr Refunds, Cr wherever the money went back
 *
 * Before this, marking a booking paid in the back office changed a word on the
 * booking and nothing else: no invoice, no receipt, nothing in the ledger. The
 * mobile app raised its own invoice and receipt inline, which is why the two
 * paths disagreed. Both go through here now.
 */
class BillingService
{
    public function __construct(
        protected LedgerService $ledger,
        protected CustomerWalletService $wallet,
    ) {}

    /* =====================================================================
     | Booking -> invoice -> receipt
     * ================================================================== */

    /** Statuses that mean the sale is committed and should be invoiced. */
    public const BILLABLE = ['confirmed', 'paid'];

    /**
     * Methods the desk can take a service payment in. Wallet is deliberately
     * absent: paying from the customer wallet needs the balance guard that
     * only the tour-booking path has.
     */
    public const PAYMENT_METHODS = ['Cash', 'Bank', 'Card', 'bKash', 'Nagad'];

    /**
     * Keep a booking's billing in step with the booking itself. Called from
     * BookingObserver, so it holds for the admin form, the API and importers.
     */
    public function syncBooking(Booking $booking): void
    {
        if ($booking->status === 'cancelled') {
            $this->withdrawUnpaidInvoice($booking);

            return;
        }

        if (! in_array($booking->status, self::BILLABLE, true) || (float) $booking->amount <= 0) {
            return;
        }

        $invoice = $this->invoiceFor($booking);

        if ($booking->status === 'paid' && $invoice) {
            $this->recordPayment($invoice, $booking->payment_method ?: 'Cash', $booking);
        }
    }

    /**
     * The invoice for a booking, created if it does not exist yet. One invoice
     * per booking: the mobile app's inline invoice is found and reused rather
     * than duplicated.
     */
    public function invoiceFor(Booking $booking): ?Invoice
    {
        $invoice = Invoice::where('booking_id', $booking->id)->first();

        if ($invoice) {
            // Amount can still change while nothing has been received.
            if ((float) $invoice->paid_amount <= 0 && (float) $invoice->amount !== (float) $booking->amount) {
                $invoice->update(['amount' => $booking->amount]);
            }

            return $invoice;
        }

        return Invoice::create([
            'customer_id'   => $booking->customer_id,
            'booking_id'    => $booking->id,
            'invoice_no'    => 'INV-B' . str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT),
            'customer_name' => $booking->customer_name ?: (Customer::find($booking->customer_id)?->name ?? 'Walk-in'),
            'issue_date'    => now()->toDateString(),
            'due_date'      => $booking->travel_date?->toDateString() ?? now()->addDays(14)->toDateString(),
            'amount'        => $booking->amount,
        ]);
    }

    /**
     * Receipt for whatever is still outstanding on an invoice. Idempotent: an
     * invoice already settled produces nothing, so re-saving a paid booking
     * cannot collect the same money twice.
     */
    public function recordPayment(Invoice $invoice, string $method = 'Cash', ?Booking $booking = null, ?string $reference = null): ?Receipt
    {
        $outstanding = round((float) $invoice->amount - (float) $invoice->paid_amount, 2);

        if ($outstanding <= 0.009) {
            return null;
        }

        // The wallet balance check and the receipt that debits it (via
        // syncReceiptWallet, below) run under one lock: two payments for the
        // same customer arriving together must serialize, or both could pass
        // the check against the same money before either has spent it.
        return DB::transaction(function () use ($invoice, $method, $booking, $reference, $outstanding) {
            // Paying from the wallet needs the money to actually be there.
            if ($method === 'Wallet' && $invoice->customer_id) {
                $this->wallet->lockCustomer((int) $invoice->customer_id);
                $balance = $this->wallet->balance((int) $invoice->customer_id);
                if ($balance + 0.009 < $outstanding) {
                    $method = 'Cash'; // not enough in the wallet: treat as cash taken at the desk
                }
            }

            $receipt = Receipt::create([
                'invoice_id'    => $invoice->id,
                'customer_id'   => $invoice->customer_id,
                'receipt_no'    => $this->nextReceiptNumber($invoice),
                'customer_name' => $invoice->customer_name,
                'received_on'   => now()->toDateString(),
                'amount'        => $outstanding,
                'method'        => $method,
                'reference'     => $reference ?: ($booking ? 'BKG-' . $booking->id : $invoice->invoice_no),
            ]);

            $this->syncReceiptWallet($receipt);

            return $receipt;
        });
    }

    /**
     * A receipt paid from the wallet takes the money out of the wallet as
     * well. The journal entry comes from the receipt itself (Dr Customer
     * Wallet, Cr Accounts Receivable), so this line is not posted again.
     */
    public function syncReceiptWallet(Receipt $receipt): void
    {
        if ($receipt->method !== 'Wallet' || ! $receipt->customer_id) {
            $this->wallet->removeEntryFor($receipt);

            return;
        }

        $this->wallet->entryFor($receipt, (int) $receipt->customer_id, 'debit', $receipt->amount,
            $receipt->received_on, 'Paid ' . ($receipt->invoice->invoice_no ?? 'invoice') . ' from wallet',
            $receipt->receipt_no);
    }

    /** A cancelled booking that nobody has paid takes its invoice with it. */
    protected function withdrawUnpaidInvoice(Booking $booking): void
    {
        $invoice = Invoice::where('booking_id', $booking->id)->first();

        if ($invoice && (float) $invoice->paid_amount <= 0 && ! $invoice->receipts()->exists()) {
            $invoice->delete();
        }
    }

    /* =====================================================================
     | Service documents -> invoice -> receipt
     |
     | hotel_bookings, transport_bookings, event_bookings and hajj_pilgrims
     | each get the treatment Booking got: their observer calls the sync
     | method below on every save, and the invoice/receipt follow the row —
     | admin form, mobile API and seeders all behave alike.
     * ================================================================== */

    public function syncHotelBooking(HotelBooking $booking): void
    {
        $this->syncServiceSale($booking, [
            'billable'      => in_array($booking->status, ['Confirmed', 'Paid'], true),
            'cancelled'     => $booking->status === 'Cancelled',
            'amount'        => (float) $booking->amount,
            'paid_target'   => $booking->status === 'Paid' ? (float) $booking->amount : 0.0,
            'method'        => $booking->payment_method ?: 'Cash',
            'customer_id'   => $booking->customer_id,
            'customer_name' => $booking->guest_name ?: ($booking->customer->name ?? 'Walk-in'),
            'due_date'      => $booking->check_in?->toDateString(),
            'prefix'        => 'INV-H',
            'reference'     => $booking->booking_no,
        ]);
    }

    public function syncTransportBooking(TransportBooking $trip): void
    {
        $this->syncServiceSale($trip, [
            // A completed trip is earned revenue even if the fare is still owed.
            'billable'      => in_array($trip->status, ['Confirmed', 'Paid', 'Completed'], true),
            'cancelled'     => $trip->status === 'Cancelled',
            'amount'        => (float) $trip->fare,
            'paid_target'   => $trip->status === 'Paid' ? (float) $trip->fare : 0.0,
            'method'        => $trip->payment_method ?: 'Cash',
            'customer_id'   => $trip->customer_id,
            'customer_name' => $trip->customer_name ?: ($trip->customer->name ?? 'Walk-in'),
            'due_date'      => $trip->travel_date?->toDateString(),
            'prefix'        => 'INV-T',
            'reference'     => $trip->booking_no,
        ]);
    }

    public function syncFlightBooking(FlightBooking $ticket): void
    {
        // A flight is a sale once the desk has ticketed it: ticket number on
        // the row and a fare. There is no "paid" state on a ticket, so the
        // billable moment is ticketing itself — the money follows, and the
        // invoice sits in AR until the agency settles it (refunds go through
        // the refund flow). Before this observer existed, a sold ticket never
        // reached the books at all.
        $this->syncServiceSale($ticket, [
            'billable'      => filled($ticket->ticket_no)
                                && (float) $ticket->fare > 0
                                && ! in_array($ticket->status, ['Cancelled', 'Refunded'], true),
            'cancelled'     => in_array($ticket->status, ['Cancelled', 'Refunded'], true),
            'amount'        => (float) $ticket->fare,
            'paid_target'   => 0.0,
            'customer_id'   => $ticket->customer_id,
            'customer_name' => $ticket->passenger_name ?: ($ticket->customer->name ?? 'Walk-in'),
            'due_date'      => $ticket->flight_date?->toDateString(),
            'prefix'        => 'INV-F',
            'reference'     => $ticket->pnr ?: 'FLT-' . $ticket->id,
        ]);
    }

    public function syncEventBooking(EventBooking $booking): void
    {
        $this->syncServiceSale($booking, [
            'billable'      => in_array($booking->status, ['Confirmed', 'Paid'], true),
            'cancelled'     => $booking->status === 'Cancelled',
            'amount'        => (float) $booking->amount,
            'paid_target'   => $booking->status === 'Paid' ? (float) $booking->amount : 0.0,
            'method'        => $booking->payment_method ?: 'Cash',
            'customer_id'   => $booking->customer_id,
            'customer_name' => $booking->customer_name ?: ($booking->customer->name ?? 'Walk-in'),
            'due_date'      => $booking->eventTour?->event_date?->toDateString(),
            'prefix'        => 'INV-E',
            'reference'     => $booking->booking_no,
        ]);
    }

    /**
     * A pilgrim's committed total is what they have paid plus what they still
     * owe; instalments arrive over months, so the receipt target follows
     * amount_paid rather than a status word. The row's own amount_paid /
     * amount_due stay the operational source of truth — the invoice mirrors
     * them into the books.
     */
    public function syncHajjPilgrim(HajjPilgrim $pilgrim): void
    {
        $total = round((float) $pilgrim->amount_paid + (float) $pilgrim->amount_due, 2);

        if ($pilgrim->status === 'Cancelled') {
            $this->withdrawUnpaidInvoiceFor($pilgrim);

            return;
        }

        if (! in_array($pilgrim->status, ['Registered', 'Confirmed'], true) || $total <= 0) {
            return;
        }

        $invoice = $this->invoiceForSource($pilgrim, [
            'amount'        => $total,
            'customer_id'   => $pilgrim->customer_id,
            'customer_name' => $pilgrim->name,
            'due_date'      => $pilgrim->departure_date?->toDateString(),
            'prefix'        => 'INV-P',
        ]);

        // Unlike a fixed-price sale, a pilgrim's total can be repriced while
        // instalments are already in — the invoice grows with it, as long as
        // it never says less than what has been received. Repriced BEFORE the
        // receipt below, or a same-save reprice+payment would cap the receipt
        // at the old total.
        if ((float) $invoice->amount !== $total && $total >= (float) $invoice->paid_amount) {
            $invoice->update(['amount' => $total]);
        }

        if ((float) $pilgrim->amount_paid > 0) {
            $this->recordPaymentUpTo($invoice, (float) $pilgrim->amount_paid,
                $pilgrim->paymentMethodHint ?: 'Cash', $pilgrim->pilgrim_no);
        }
    }

    /**
     * The shared engine behind the four service syncs. Mirrors syncBooking:
     * cancelled documents withdraw an unpaid invoice, billable ones raise or
     * re-price it, and a receipt is recorded for whatever the paid target says
     * should have arrived by now. Idempotent on every path.
     */
    protected function syncServiceSale(Model $source, array $sale): ?Invoice
    {
        if (! empty($sale['cancelled'])) {
            $this->withdrawUnpaidInvoiceFor($source);

            return null;
        }

        if (empty($sale['billable']) || (float) $sale['amount'] <= 0) {
            return null;
        }

        $invoice = $this->invoiceForSource($source, $sale);

        if ((float) ($sale['paid_target'] ?? 0) > 0) {
            $this->recordPaymentUpTo($invoice, (float) $sale['paid_target'],
                $sale['method'] ?? 'Cash', $sale['reference'] ?? null);
        }

        return $invoice;
    }

    /** The invoice for a service document, created if it does not exist yet. */
    public function invoiceForSource(Model $source, array $sale): Invoice
    {
        $invoice = Invoice::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->first();

        if ($invoice) {
            // Amount can still change while nothing has been received.
            if ((float) $invoice->paid_amount <= 0 && (float) $invoice->amount !== (float) $sale['amount']) {
                $invoice->update(['amount' => $sale['amount']]);
            }

            return $invoice;
        }

        return Invoice::create([
            'customer_id'   => $sale['customer_id'] ?? null,
            'source_type'   => $source->getMorphClass(),
            'source_id'     => $source->getKey(),
            'invoice_no'    => ($sale['prefix'] ?? 'INV-S') . str_pad((string) $source->getKey(), 5, '0', STR_PAD_LEFT),
            'customer_name' => $sale['customer_name'] ?? 'Walk-in',
            'issue_date'    => now()->toDateString(),
            'due_date'      => $sale['due_date'] ?? now()->addDays(14)->toDateString(),
            'amount'        => $sale['amount'],
        ]);
    }

    /**
     * Bring an invoice's receipts up to a running total. Instalments each get
     * their own receipt for the difference; a target at or below what has
     * already been received records nothing, so re-saving cannot collect the
     * same money twice.
     */
    public function recordPaymentUpTo(Invoice $invoice, float $target, string $method = 'Cash', ?string $reference = null): ?Receipt
    {
        $target = min($target, (float) $invoice->amount);
        $delta  = round($target - (float) $invoice->paid_amount, 2);

        if ($delta <= 0.009) {
            return null;
        }

        return Receipt::create([
            'invoice_id'    => $invoice->id,
            'customer_id'   => $invoice->customer_id,
            'receipt_no'    => $this->nextReceiptNumber($invoice),
            'customer_name' => $invoice->customer_name,
            'received_on'   => now()->toDateString(),
            'amount'        => $delta,
            'method'        => $method,
            'reference'     => $reference ?: $invoice->invoice_no,
        ]);
    }

    /** A cancelled or deleted service document that nobody has paid takes its invoice with it. */
    public function withdrawUnpaidInvoiceFor(Model $source): void
    {
        $invoice = Invoice::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->first();

        if ($invoice && (float) $invoice->paid_amount <= 0 && ! $invoice->receipts()->exists()) {
            $invoice->delete();
        }
    }

    /* =====================================================================
     | Refunds
     * ================================================================== */

    /**
     * Give money back. The invoice and the sale stay as they were — a refund
     * is posted against them (Dr Refunds, Cr cash/bank/wallet), so what
     * happened is still readable afterwards.
     */
    public function refund(Invoice $invoice, float $amount, string $method = 'Cash', ?string $reason = null): Refund
    {
        return Refund::create([
            'invoice_id'   => $invoice->id,
            'booking_id'   => $invoice->booking_id,
            'customer_id'  => $invoice->customer_id,
            'reference'    => $this->nextRefundReference(),
            'amount'       => $amount,
            'method'       => $method,
            'refunded_on'  => now()->toDateString(),
            'reason'       => $reason,
            'processed_by' => auth()->id(),
        ]);
    }

    /** What may still be refunded on an invoice: received less already given back. */
    public function refundableAmount(Invoice $invoice): float
    {
        return round((float) $invoice->paid_amount - (float) $invoice->refunded_amount, 2);
    }

    /**
     * Post a refund: Dr Refunds, Cr the account the money went back through.
     * A wallet refund also puts the credit in the customer's wallet, and that
     * line is not posted separately — this entry is its other leg.
     */
    public function postRefund(Refund $refund): void
    {
        $refunds = Account::system('refund');
        $money   = Account::system(match ($refund->method) {
            'Wallet' => 'customer_wallet',
            'Bank'   => 'bank',
            default  => 'cash',
        });

        $this->ledger->postForSource($refund, [
            'account_id'        => $refunds?->id,
            'contra_account_id' => $money?->id,
            'txn_date'          => $refund->refunded_on,
            'account_name'      => $refunds->name ?? 'Refunds',
            'type'              => 'expense',
            'amount'            => $refund->amount,
            'reference'         => $refund->reference,
            'description'       => 'Refund ' . $refund->reference . ' — '
                                    . ($refund->invoice->invoice_no ?? 'invoice')
                                    . ($refund->reason ? ' (' . $refund->reason . ')' : ''),
        ]);

        $this->syncRefundWallet($refund);
        $this->ledger->reconcileInvoice($refund->invoice()->first());
    }

    /** Wallet refunds land in the customer's wallet as a credit. */
    public function syncRefundWallet(Refund $refund): void
    {
        if (! $refund->toWallet() || ! $refund->customer_id) {
            $this->wallet->removeEntryFor($refund);

            return;
        }

        $this->wallet->entryFor($refund, (int) $refund->customer_id, 'credit', $refund->amount,
            $refund->refunded_on, 'Refund ' . $refund->reference
                . ($refund->booking_id ? ' for booking #' . $refund->booking_id : ''),
            $refund->reference);
    }

    /**
     * Refund a paid booking in full and cancel it — what the mobile app's
     * "cancel my booking" does, now with the books following.
     */
    public function refundBooking(Booking $booking, string $method = 'Wallet', ?string $reason = null): ?Refund
    {
        $invoice = Invoice::where('booking_id', $booking->id)->first();

        if (! $invoice) {
            return null;
        }

        $amount = $this->refundableAmount($invoice);

        if ($amount <= 0.009) {
            return null;
        }

        return $this->refund($invoice, $amount, $method, $reason ?: 'Booking #' . $booking->id . ' cancelled');
    }

    /**
     * A refund with no invoice behind it — a flight ticket settled against the
     * airline, for instance, where the fare was never invoiced here. It still
     * posts (Dr Refunds, Cr cash/bank/wallet) via RefundObserver, so the money
     * leaving is in the books even when no sale ever was.
     */
    public function refundStandalone(float $amount, string $method = 'Cash', ?int $customerId = null, ?Invoice $invoice = null, ?string $reason = null): ?Refund
    {
        if ($amount <= 0.009) {
            return null;
        }

        return Refund::create([
            'invoice_id'   => $invoice?->id,
            'booking_id'   => $invoice?->booking_id,
            'customer_id'  => $customerId ?? $invoice?->customer_id,
            'reference'    => $this->nextRefundReference(),
            'amount'       => $amount,
            'method'       => $method,
            'refunded_on'  => now()->toDateString(),
            'reason'       => $reason,
            'processed_by' => auth()->id(),
        ]);
    }

    /* =====================================================================
     | References
     * ================================================================== */

    private function nextReceiptNumber(Invoice $invoice): string
    {
        $base = 'RCP-' . str_replace(['INV-', 'INV'], '', $invoice->invoice_no);
        $no   = $base;
        $i    = 1;

        // Part payments on the same invoice each need their own number.
        while (Receipt::where('receipt_no', $no)->exists()) {
            $no = $base . '-' . (++$i);
        }

        return $no;
    }

    private function nextRefundReference(): string
    {
        $lastId = (int) Refund::max('id');

        return 'RFD-' . str_pad((string) ($lastId + 1001), 4, '0', STR_PAD_LEFT);
    }
}
