<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\CrmActivity;
use App\Models\FlightBooking;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadFlightConversionTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
    }

    private function flightLead(): Lead
    {
        return Lead::create([
            'name' => 'Flight enquiry',
            'interest' => 'Flight Booking',
            'source' => 'Website',
            'stage' => 'New',
        ]);
    }

    public function test_flight_conversion_page_is_unavailable_to_super_admin(): void
    {
        $lead = $this->flightLead();

        $this->actingAs($this->admin())
            ->get(route('crm.leads.convert.flight', $lead->id))
            ->assertNotFound();
    }

    public function test_direct_flight_conversion_cannot_create_a_booking_or_close_the_lead(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $lead = $this->flightLead();
        $before = FlightBooking::count();

        $this->actingAs($this->admin())
            ->post(route('crm.leads.store.flight', $lead->id), [
                'pnr' => 'ABC123',
                'passenger_name' => $lead->name,
                'airline' => 'Emirates',
                'route' => 'Dhaka -> Dubai',
                'flight_date' => '2026-10-01',
                'fare' => 42500,
                'status' => 'Pending',
            ])->assertNotFound();

        $this->assertSame($before, FlightBooking::count());
        $this->assertSame('New', $lead->fresh()->stage);
        $this->assertSame(0, CrmActivity::where('lead_id', $lead->id)->count());
    }

    public function test_lead_details_do_not_advertise_flight_conversion(): void
    {
        $lead = $this->flightLead();

        $this->actingAs($this->admin())
            ->get(route('crm.leads.show', $lead->id))
            ->assertOk()
            ->assertDontSee(route('crm.leads.convert.flight', $lead->id), false);
    }
}
