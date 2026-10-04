<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Services\Documents\DocumentService;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ResolvesCustomer;
use Illuminate\Http\Request;

/**
 * PDF downloads for the mobile app: invoice, receipt, hotel voucher, e-ticket.
 *
 * Every lookup is scoped to the signed-in customer, so an id from someone
 * else's account returns 404 rather than their paperwork. The response is the
 * file itself (inline), which is what the app hands to the system viewer or
 * share sheet.
 */
class DocumentPdfController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer;

    public function __construct(private DocumentService $documents) {}

    public function invoice(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can download documents.', [], 403);
        }

        $invoice = Invoice::where('customer_id', $customer->id)->find($id);
        if (! $invoice) {
            return $this->responseWithError('Invoice not found.', [], 404);
        }

        return $this->documents->invoice($invoice)
            ->stream($this->documents->filename('invoice', $invoice->invoice_no));
    }

    public function receipt(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can download documents.', [], 403);
        }

        $receipt = Receipt::where('customer_id', $customer->id)->find($id);
        if (! $receipt) {
            return $this->responseWithError('Receipt not found.', [], 404);
        }

        return $this->documents->receipt($receipt)
            ->stream($this->documents->filename('receipt', $receipt->receipt_no));
    }

    public function hotelVoucher(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can download documents.', [], 403);
        }

        $booking = HotelBooking::where('customer_id', $customer->id)->find($id);
        if (! $booking) {
            return $this->responseWithError('Hotel booking not found.', [], 404);
        }

        // A cancelled stay has no voucher to present at a front desk.
        if ($booking->status === 'Cancelled') {
            return $this->responseWithError('A cancelled booking has no voucher.', [], 409);
        }

        return $this->documents->hotelVoucher($booking)
            ->stream($this->documents->filename('voucher', $booking->booking_no));
    }

    public function eTicket(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can download documents.', [], 403);
        }

        $ticket = FlightBooking::where('customer_id', $customer->id)->find($id);
        if (! $ticket) {
            return $this->responseWithError('Ticket not found.', [], 404);
        }

        return $this->documents->eTicket($ticket)
            ->stream($this->documents->filename('eticket', $ticket->ticket_no ?: $ticket->pnr));
    }
}
