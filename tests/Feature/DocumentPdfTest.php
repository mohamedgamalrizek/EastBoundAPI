<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\FlightBooking;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Generated paperwork.
 *
 * Two things matter: the documents actually render as PDFs (a broken Blade in
 * a print template is invisible until someone downloads it), and one customer
 * can never fetch another's by putting a different id in the URL.
 */
class DocumentPdfTest extends TestCase
{
    use RefreshDatabase;

    private function customer(string $phone = '01799000111'): Customer
    {
        return Customer::create([
            'name'     => 'Tanvir Ahmed',
            'phone'    => $phone,
            'email'    => $phone . '@example.com',
            'password' => 'secret123',
            'status'   => 'active',
        ]);
    }

    private function hotel(string $name): Hotel
    {
        return Hotel::create([
            'name'            => $name,
            'city'            => "Cox's Bazar",
            'country'         => 'Bangladesh',
            'category'        => 4,
            'rooms_count'     => 40,
            'price_per_night' => 6000,
            'status'          => 'Active',
        ]);
    }

    private function room(Hotel $hotel): HotelRoom
    {
        return HotelRoom::create([
            'hotel_id'        => $hotel->id,
            'room_type'       => 'Deluxe Twin',
            'capacity'        => 2,
            'rate_per_night'  => 6000,
            'total_rooms'     => 10,
            'available_rooms' => 10,
        ]);
    }

    private function paidBookingInvoice(Customer $customer): Invoice
    {
        $booking = Booking::create([
            'customer_id'    => $customer->id,
            'customer_name'  => $customer->name,
            'booking_no'     => 'BKG-PDF-' . $customer->id,
            'status'         => 'paid',
            'payment_method' => 'Cash',
            'amount'         => 18000,
            'travel_date'    => now()->addMonth()->toDateString(),
        ]);

        // The observers invoice and receipt it.
        return Invoice::where('booking_id', $booking->id)->firstOrFail();
    }

    private function assertIsPdf($response): void
    {
        $response->assertOk();

        // dompdf hands back a plain response with the whole file in it, so the
        // magic bytes are the honest check that a PDF (not a Blade error page)
        // came out the other end.
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_a_customer_can_download_their_invoice_and_receipt(): void
    {
        $customer = $this->customer();
        $invoice  = $this->paidBookingInvoice($customer);
        $receipt  = $invoice->receipts()->firstOrFail();

        $this->assertIsPdf(
            $this->actingAs($customer, 'sanctum')->get("/api/v1/invoices/{$invoice->id}/pdf")
        );

        $this->assertIsPdf(
            $this->actingAs($customer, 'sanctum')->get("/api/v1/receipts/{$receipt->id}/pdf")
        );
    }

    public function test_a_hotel_voucher_and_e_ticket_render(): void
    {
        $customer = $this->customer('01799000222');

        $hotel = $this->hotel('Sea Pearl Beach Resort');

        $stay = HotelBooking::create([
            'booking_no'  => 'HB-PDF-1',
            'hotel_id'      => $hotel->id,
            'hotel_room_id' => $this->room($hotel)->id,
            'customer_id'   => $customer->id,
            'guest_name'  => $customer->name,
            'check_in'    => now()->addWeek()->toDateString(),
            'check_out'   => now()->addWeeks(2)->toDateString(),
            'nights'      => 7,
            'amount'      => 42000,
            'status'      => 'Confirmed',
        ]);

        $ticket = FlightBooking::create([
            'customer_id'    => $customer->id,
            'pnr'            => 'ABC123',
            'passenger_name' => $customer->name,
            'airline'        => 'Biman Bangladesh',
            'route'          => 'DAC → DXB',
            'flight_date'    => now()->addMonth()->toDateString(),
            'ticket_no'      => '997-1234567890',
            'fare'           => 68000,
            'status'         => 'Confirmed',
        ]);

        $this->assertIsPdf(
            $this->actingAs($customer, 'sanctum')->get("/api/v1/hotel-bookings/{$stay->id}/voucher")
        );

        $this->assertIsPdf(
            $this->actingAs($customer, 'sanctum')->get("/api/v1/flights/{$ticket->id}/eticket")
        );
    }

    public function test_one_customer_cannot_download_anothers_invoice(): void
    {
        $owner     = $this->customer('01799000333');
        $outsider  = $this->customer('01799000444');
        $invoice   = $this->paidBookingInvoice($owner);

        $this->actingAs($outsider, 'sanctum')
            ->getJson("/api/v1/invoices/{$invoice->id}/pdf")
            ->assertStatus(404);
    }

    public function test_a_cancelled_stay_has_no_voucher(): void
    {
        $customer = $this->customer('01799000555');

        $hotel = $this->hotel('Long Beach Hotel');

        $stay = HotelBooking::create([
            'booking_no'  => 'HB-PDF-2',
            'hotel_id'      => $hotel->id,
            'hotel_room_id' => $this->room($hotel)->id,
            'customer_id'   => $customer->id,
            'guest_name'  => $customer->name,
            'check_in'    => now()->addWeek()->toDateString(),
            'check_out'   => now()->addWeeks(2)->toDateString(),
            'nights'      => 7,
            'amount'      => 30000,
            'status'      => 'Cancelled',
        ]);

        $this->actingAs($customer, 'sanctum')
            ->getJson("/api/v1/hotel-bookings/{$stay->id}/voucher")
            ->assertStatus(409);
    }
}
