<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Services\Documents\DocumentService;

/**
 * Back-office PDF downloads. Each route already carries the permission the
 * matching module uses, so nobody gains sight of a document they could not
 * open on screen.
 */
class DocumentController extends Controller
{
    public function __construct(private DocumentService $documents) {}

    public function invoice($id)
    {
        $invoice = Invoice::findOrFail($id);

        return $this->documents->invoice($invoice)
            ->download($this->documents->filename('invoice', $invoice->invoice_no));
    }

    public function receipt($id)
    {
        $receipt = Receipt::findOrFail($id);

        return $this->documents->receipt($receipt)
            ->download($this->documents->filename('receipt', $receipt->receipt_no));
    }

    public function hotelVoucher($id)
    {
        $booking = HotelBooking::findOrFail($id);

        return $this->documents->hotelVoucher($booking)
            ->download($this->documents->filename('voucher', $booking->booking_no));
    }

    public function eTicket($id)
    {
        $ticket = FlightBooking::findOrFail($id);

        return $this->documents->eTicket($ticket)
            ->download($this->documents->filename('eticket', $ticket->ticket_no ?: $ticket->pnr));
    }
}
