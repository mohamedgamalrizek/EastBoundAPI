<?php

namespace Tests\Feature;

use App\Models\CrmActivity;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A hotel enquiry from the website lands in the CRM as a Lead; the desk
 * converts it into a real HotelBooking (hotel, room, amount) from the lead.
 */
class LeadHotelConversionTest extends TestCase
{
    // Runs against the dedicated flow_test database (see phpunit.xml).
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
    }

    private function hotelLead(): Lead
    {
        return Lead::create([
            'name'     => 'Salma Akter',
            'phone'    => '+8801711223344',
            'email'    => 'salma@example.com',
            'interest' => 'Hotel Booking',
            'source'   => 'Website',
            'stage'    => 'New',
            'notes'    => "Hotel Booking request.\n"
                . "City: Cox's Bazar\nHotel: Sea Pearl\nCheckin: 2026-10-01\n"
                . "Checkout: 2026-10-05\nGuests: 2 guests, 1 room\n\nOcean view preferred.",
        ]);
    }

    public function test_convert_page_prefills_from_lead_notes(): void
    {
        $lead = $this->hotelLead();

        $response = $this->actingAs($this->admin())
            ->get(route('crm.leads.convert.hotel', $lead->id));

        $response->assertOk()
            ->assertSee('Salma Akter')
            ->assertSee('2026-10-01')
            ->assertSee('2026-10-05')
            ->assertSee('value="4"', false); // nights between check-in and check-out
    }

    public function test_storing_booking_creates_hotel_booking_and_closes_lead(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $lead = $this->hotelLead();
        $room = HotelRoom::firstOrFail();

        $response = $this->actingAs($this->admin())
            ->post(route('crm.leads.store.hotel', $lead->id), [
                'booking_no'    => 'HTL-1001',
                'hotel_id'      => $room->hotel_id,
                'hotel_room_id' => $room->id,
                'guest_name'    => $lead->name,
                'check_in'      => '2026-10-01',
                'check_out'     => '2026-10-05',
                'nights'        => 4,
                'amount'        => 24000,
                'status'        => 'Booked',
            ]);

        $booking = HotelBooking::where('booking_no', 'HTL-1001')->first();
        $this->assertNotNull($booking, 'converted hotel booking was not created');

        $response->assertRedirect(route('hotel.booking.index'));

        $this->assertDatabaseHas('hotel_bookings', [
            'booking_no'    => 'HTL-1001',
            'hotel_id'      => $room->hotel_id,
            'hotel_room_id' => $room->id,
            'guest_name'    => 'Salma Akter',
            'check_in'      => '2026-10-01',
            'check_out'     => '2026-10-05',
            'nights'        => 4,
            'amount'        => 24000,
            'status'        => 'Booked',
        ]);

        $lead->refresh();
        $this->assertSame('Won', $lead->stage);
        $this->assertStringContainsString('Converted to hotel booking', $lead->notes);

        $this->assertDatabaseHas('crm_activities', [
            'lead_id' => $lead->id,
            'subject' => 'Converted to hotel booking',
        ]);
    }

    public function test_conversion_validates_required_booking_fields(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $lead = $this->hotelLead();

        $response = $this->actingAs($this->admin())
            ->post(route('crm.leads.store.hotel', $lead->id), [
                'booking_no' => 'HTL-1002',
            ]);

        $response->assertSessionHasErrors(['hotel_id', 'hotel_room_id', 'guest_name', 'check_in', 'check_out', 'nights', 'amount', 'status']);
        $this->assertSame(0, HotelBooking::where('booking_no', 'HTL-1002')->count());
        $this->assertSame(0, CrmActivity::where('lead_id', $lead->id)->count());
    }
}
