<?php

namespace Tests\Feature;

use App\Models\AgentCommission;
use App\Models\Customer;
use App\Models\FlightBooking;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 04 — an agent sells a flight to a client, from the portal form or the
 * app, and the FlightBookingObserver posts the sale to the books.
 *
 * Without a live GDS the request opens Pending at fare 0 and the desk prices
 * it and issues the ticket (PNR, ticket number, fare). The rules worth
 * holding on to: the client gets a customer record, the request is stamped
 * with the agent, a placeholder PNR keeps pending requests unique (the pnr
 * column is unique and the customer app writes ''), a repeated submit returns
 * the request already open, and the observer raises the invoice and the
 * agent's commission only once the ticket is issued — and withdraws them
 * again if the ticket is cancelled before anything is paid.
 */
class AgentFlightSaleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function agent(): User
    {
        $role = Role::where('name', 'Agent')->first()
            ?? Role::create(['name' => 'Agent', 'slug' => 'agent']);

        return User::factory()->create([
            'role_id'         => $role->id,
            'commission_rate' => 10,
        ]);
    }

    private function customer(): Customer
    {
        return Customer::create([
            'name'     => 'Tanvir Ahmed',
            'phone'    => '01712340001',
            'email'    => 'tanvir@example.com',
            'password' => 'secret123',
            'status'   => 'active',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'route'          => 'DAC-DXB',
            'flight_date'    => now()->addDays(10)->toDateString(),
            'passenger_name' => 'Nusrat Jahan',
            'airline'        => 'BG',
            'client_name'    => 'Tanvir Ahmed',
            'client_phone'   => '01712340001',
        ], $overrides);
    }

    public function test_the_portal_flight_form_renders_and_a_request_is_filed(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent)->get(route('agent.flight.create'))->assertOk();

        $this->actingAs($agent)
            ->post(route('agent.flight.store'), $this->payload())
            ->assertRedirect(route('agent.bookings'));

        $flight = FlightBooking::where('agent_id', $agent->id)->firstOrFail();
        $this->assertSame('Pending', $flight->status);
        $this->assertEqualsWithDelta(0, (float) $flight->fare, 0.01);
        $this->assertStringStartsWith('REQ-', (string) $flight->pnr,
            'A pending request needs a unique placeholder PNR, not an empty one.');
        $this->assertSame('Nusrat Jahan', $flight->passenger_name);
        $this->assertNotNull($flight->customer_id, 'The client must get a customer record.');
    }

    /** The form asks for the two ends of the flight, not one route string. */
    private function fromToPayload(array $overrides = []): array
    {
        $payload = $this->payload($overrides);
        unset($payload['route']);

        return array_merge(['from' => 'DAC', 'to' => 'DXB'], $payload);
    }

    public function test_the_form_asks_for_from_and_to_and_the_two_compose_the_route(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent)
            ->get(route('agent.flight.create'))
            ->assertOk()
            ->assertSee('name="from"', false)
            ->assertSee('name="to"', false);

        $this->actingAs($agent)
            ->post(route('agent.flight.store'), $this->fromToPayload())
            ->assertRedirect(route('agent.bookings'));

        $this->assertSame('DAC-DXB', FlightBooking::where('agent_id', $agent->id)->firstOrFail()->route,
            'From and To are joined into the single route the booking stores.');
    }

    public function test_a_half_written_or_circular_route_is_rejected(): void
    {
        $agent = $this->agent();

        // The class seeds demo data, so the table is not empty to begin with.
        // What matters is that a rejected submit adds nothing to it.
        $before = FlightBooking::count();

        $this->actingAs($agent)
            ->post(route('agent.flight.store'), $this->fromToPayload(['to' => '']))
            ->assertSessionHasErrors('to');

        $this->actingAs($agent)
            ->post(route('agent.flight.store'), $this->fromToPayload(['from' => '']))
            ->assertSessionHasErrors('from');

        $this->actingAs($agent)
            ->post(route('agent.flight.store'), $this->fromToPayload(['to' => 'DAC']))
            ->assertSessionHasErrors('to');

        $this->assertSame($before, FlightBooking::count(),
            'A rejected submit must not write a booking.');
        $this->assertSame(0, FlightBooking::where('agent_id', $agent->id)->count(),
            'and nothing may be filed against the agent who submitted it.');
    }

    public function test_the_app_files_a_request_from_the_from_and_to_boxes(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent, 'sanctum')
            ->postJson('/api/v1/agent/flight-bookings', $this->fromToPayload())
            ->assertStatus(201)
            ->assertJsonPath('data.flight.route', 'DAC-DXB');
    }

    public function test_the_app_endpoint_files_the_same_kind_of_request_with_no_fare(): void
    {
        $agent = $this->agent();

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson('/api/v1/agent/flight-bookings', $this->payload([
                'fare' => 9999, // a client-side number the server must ignore
                'pnr'  => 'HACKED',
            ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.flight.status', 'Pending')
            ->assertJsonPath('data.flight.route', 'DAC-DXB');

        $this->assertEqualsWithDelta(0, (float) $response->json('data.flight.fare'), 0.01);
        $this->assertStringStartsWith('REQ-', (string) $response->json('data.flight.pnr'));
    }

    public function test_placeholder_pnrs_keep_pending_requests_distinct(): void
    {
        $first  = $this->agent();
        $second = $this->agent();
        // APP_DEMO=true seeds real FlightBooking rows before every test, so
        // the total to check against is a baseline + 2, not a bare 2.
        $before = FlightBooking::count();

        // Two agents selling the same client the same route must each get
        // their own row — and the unique pnr index must not stop them, as it
        // would if both wrote ''.
        $this->actingAs($first)->post(route('agent.flight.store'), $this->payload());
        $this->actingAs($second)->post(route('agent.flight.store'), $this->payload());

        $this->assertSame($before + 2, FlightBooking::count());
        $this->assertSame(1, FlightBooking::where('agent_id', $first->id)->count());
        $this->assertSame(1, FlightBooking::where('agent_id', $second->id)->count());
    }

    public function test_a_repeated_submit_returns_the_same_request(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent)->post(route('agent.flight.store'), $this->payload());
        $this->actingAs($agent)->post(route('agent.flight.store'), $this->payload());

        $this->assertSame(1, FlightBooking::where('agent_id', $agent->id)->count());
    }

    public function test_the_commission_lands_only_once_the_ticket_is_issued(): void
    {
        $agent  = $this->agent();
        $flight = FlightBooking::create([
            'customer_id'    => $this->customer()->id,
            'agent_id'       => $agent->id,
            'pnr'            => 'REQ-TEST0001',
            'ticket_no'      => null,
            'passenger_name' => 'Nusrat Jahan',
            'airline'        => 'BG',
            'route'          => 'DAC-DXB',
            'flight_date'    => now()->addDays(10)->toDateString(),
            'fare'           => 0,
            'status'         => 'Pending',
        ]);

        $this->assertSame(0, AgentCommission::where('agent_id', $agent->id)->count(),
            'A pending, un-ticketed request earns nothing.');

        // The desk issues the ticket: PNR, ticket number and fare.
        $flight->update([
            'pnr'       => 'TEST1234',
            'ticket_no' => 'TKT-1001',
            'fare'      => 50000,
            'status'    => 'Confirmed',
        ]);

        $commission = AgentCommission::where('agent_id', $agent->id)->firstOrFail();
        $this->assertEqualsWithDelta(5000, (float) $commission->amount, 0.01,
            '10% of the issued fare, from the agent rate.');
        $this->assertSame('COM-F' . str_pad((string) $flight->id, 5, '0', STR_PAD_LEFT), $commission->reference);

        // Re-saving the same ticket must not mint a second commission.
        $flight->update(['status_note' => 'confirmed over the phone']);
        $this->assertSame(1, AgentCommission::where('agent_id', $agent->id)->count());
    }

    public function test_a_ticket_cancelled_before_payment_returns_its_commission_and_invoice(): void
    {
        $agent  = $this->agent();
        $flight = FlightBooking::create([
            'customer_id'    => $this->customer()->id,
            'agent_id'       => $agent->id,
            'pnr'            => 'REQ-TEST0002',
            'ticket_no'      => null,
            'passenger_name' => 'Nusrat Jahan',
            'airline'        => 'BG',
            'route'          => 'DAC-DXB',
            'flight_date'    => now()->addDays(10)->toDateString(),
            'fare'           => 0,
            'status'         => 'Pending',
        ]);

        $flight->update([
            'pnr'       => 'TEST5678',
            'ticket_no' => 'TKT-1002',
            'fare'      => 50000,
            'status'    => 'Confirmed',
        ]);

        $this->assertSame(1, AgentCommission::where('agent_id', $agent->id)->count());
        $this->assertSame(1, \App\Models\Invoice::where('source_type', (new FlightBooking)->getMorphClass())
            ->where('source_id', $flight->id)->count());

        // The desk cancels before taking any money.
        $flight->update(['status' => 'Cancelled']);

        $this->assertSame(0, AgentCommission::where('agent_id', $agent->id)->count(),
            'A cancelled sale stops qualifying and its pending commission is withdrawn.');
        $this->assertSame(0, \App\Models\Invoice::where('source_type', (new FlightBooking)->getMorphClass())
            ->where('source_id', $flight->id)->count(),
            'An unpaid invoice follows its cancelled ticket out.');
    }
}
