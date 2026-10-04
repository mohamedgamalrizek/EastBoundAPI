<?php

namespace Tests\Feature;

use App\Models\CrmActivity;
use App\Models\FlightBooking;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A flight enquiry from the website lands in the CRM as a Lead; the desk
 * converts it into a real FlightBooking (PNR, airline, fare) from the lead.
 */
class LeadFlightConversionTest extends TestCase
{
    // Runs against the dedicated flow_test database (see phpunit.xml).
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
    }

    private function flightLead(): Lead
    {
        return Lead::create([
            'name'     => 'Rahim Uddin',
            'phone'    => '+8801712345678',
            'email'    => 'rahim@example.com',
            'interest' => 'Flight Booking',
            'source'   => 'Website',
            'stage'    => 'New',
            'notes'    => "Flight Booking request.\n"
                . "From: Dhaka\nTo: Dubai\nDeparture Date: 2026-10-01\n"
                . "Return Date: 2026-10-08\nTravelers: 2\n\nTwo adults, window seat.",
        ]);
    }

    public function test_convert_page_prefills_from_lead_notes(): void
    {
        $lead = $this->flightLead();

        $response = $this->actingAs($this->admin())
            ->get(route('crm.leads.convert.flight', $lead->id));

        $response->assertOk()
            ->assertSee('Rahim Uddin')
            ->assertSee('Dhaka -> Dubai')
            ->assertSee('2026-10-01');
    }

    public function test_storing_booking_creates_flight_booking_and_closes_lead(): void
    {
        // Session tokens do not persist across requests in tests, so CSRF
        // would 419 every web POST (the existing POST tests fail on this
        // too). Bypass only CSRF; auth, permissions and validation still run.
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $lead = $this->flightLead();

        $response = $this->actingAs($this->admin())
            ->post(route('crm.leads.store.flight', $lead->id), [
                'pnr'            => 'ABC123',
                'passenger_name' => $lead->name,
                'airline'        => 'Emirates',
                'route'          => 'Dhaka -> Dubai',
                'flight_date'    => '2026-10-01',
                'fare'           => 42500,
                'status'         => 'Pending',
            ]);

        $flight = FlightBooking::where('pnr', 'ABC123')->first();
        $this->assertNotNull($flight, 'converted flight booking was not created');

        $response->assertRedirect(route('flight.booking', $flight->id));

        $this->assertDatabaseHas('flight_bookings', [
            'pnr'            => 'ABC123',
            'passenger_name' => 'Rahim Uddin',
            'airline'        => 'Emirates',
            'route'          => 'Dhaka -> Dubai',
            'fare'           => 42500,
            'status'         => 'Pending',
        ]);

        $lead->refresh();
        $this->assertSame('Won', $lead->stage);
        $this->assertStringContainsString('Converted to flight booking', $lead->notes);

        $this->assertDatabaseHas('crm_activities', [
            'lead_id' => $lead->id,
            'subject' => 'Converted to flight booking',
        ]);
    }

    public function test_conversion_validates_required_ticket_fields(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $lead = $this->flightLead();

        $response = $this->actingAs($this->admin())
            ->post(route('crm.leads.store.flight', $lead->id), [
                'pnr' => 'ABC123',
            ]);

        $response->assertSessionHasErrors(['passenger_name', 'airline', 'route', 'flight_date', 'fare', 'status']);
        $this->assertSame(0, FlightBooking::where('pnr', 'ABC123')->count());
        $this->assertSame(0, CrmActivity::where('lead_id', $lead->id)->count());
    }
}
