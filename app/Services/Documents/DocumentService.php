<?php

namespace App\Services\Documents;

use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\Invoice;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfWrapper;

/**
 * The agency's printable paperwork: invoice, receipt, hotel voucher, e-ticket.
 *
 * One service so the same document looks the same wherever it is asked for —
 * the back office, the customer portal and the mobile app all come through
 * here, and none of them decides for itself what an invoice looks like.
 *
 * Every document is rendered on demand rather than stored: an invoice that has
 * since been part-paid or refunded should print as it is now, not as it was
 * the first time somebody clicked download.
 */
class DocumentService
{
    public function invoice(Invoice $invoice): PdfWrapper
    {
        $invoice->loadMissing(['customer', 'receipts', 'refunds', 'booking', 'source']);

        return $this->render('pdf.invoice', [
            'invoice'         => $invoice,
            'lineDescription' => $this->describe($invoice),
        ]);
    }

    public function receipt(Receipt $receipt): PdfWrapper
    {
        $receipt->loadMissing(['customer', 'invoice']);

        return $this->render('pdf.receipt', ['receipt' => $receipt]);
    }

    public function hotelVoucher(HotelBooking $booking): PdfWrapper
    {
        $booking->loadMissing(['hotel', 'hotelRoom', 'customer']);

        return $this->render('pdf.hotel-voucher', ['booking' => $booking]);
    }

    public function eTicket(FlightBooking $ticket): PdfWrapper
    {
        $ticket->loadMissing(['customer']);

        return $this->render('pdf.eticket', ['ticket' => $ticket]);
    }

    /** Filenames a customer can recognise in their downloads folder. */
    public function filename(string $kind, string $number): string
    {
        $number = preg_replace('/[^A-Za-z0-9\-_]/', '-', $number) ?: 'document';

        return strtolower($kind) . '-' . $number . '.pdf';
    }

    private function render(string $view, array $data): PdfWrapper
    {
        return Pdf::loadView($view, $data)->setPaper('a4');
    }

    /**
     * What the invoice is for, in words. An invoice points either at a tour
     * booking or at one of the service documents (hotel, transport, event,
     * hajj), so the line comes from whichever is set.
     */
    private function describe(Invoice $invoice): string
    {
        if ($invoice->booking) {
            $reference = $invoice->booking->booking_no ?: ('Booking #' . $invoice->booking->id);

            return 'Tour booking ' . $reference;
        }

        $source = $invoice->source;

        if (! $source) {
            return 'Travel services';
        }

        $label = match ($source::class) {
            HotelBooking::class            => 'Hotel booking',
            \App\Models\TransportBooking::class => 'Transport booking',
            \App\Models\EventBooking::class     => 'Event booking',
            \App\Models\HajjPilgrim::class      => 'Hajj / Umrah registration',
            default                             => 'Travel services',
        };

        $reference = $source->booking_no ?? $source->registration_no ?? ('#' . $source->getKey());

        return trim($label . ' ' . $reference);
    }
}
