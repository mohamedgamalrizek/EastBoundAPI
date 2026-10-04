<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Role;
use App\Models\TransportBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 03 — an agent sells a transport trip to a client, from the portal
 * form or the app.
 *
 * The trip reuses the website enquiry's own booking action, so it opens
 * Pending at fare 0 for the desk to price — there is no fare for the agent to
 * send. The rules worth holding on to: the client gets a customer record, the
 * trip is stamped with the agent (their commission comes later, on payment),
 * an attributed sale raises no CRM lead, and a repeated submit returns the
 * trip already open rather than a second one.
 */
class AgentTransportSaleTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type'         => 'Airport',
            'direction'    => 'Pickup',
            'client_name'  => 'Tanvir Ahmed',
            'client_phone' => '01712340001',
            'route'        => 'DAC Airport → Gulshan',
            'travel_date'  => now()->addDays(2)->toDateString(),
            'vehicle'      => 'Sedan',
        ], $overrides);
    }

    public function test_the_portal_transport_form_renders_and_a_trip_is_opened(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent)->get(route('agent.transport.create'))->assertOk();

        $this->actingAs($agent)
            ->post(route('agent.transport.store'), $this->payload())
            ->assertRedirect(route('agent.bookings'));

        $trip = TransportBooking::where('agent_id', $agent->id)->firstOrFail();
        $this->assertSame('Pending', $trip->status);
        $this->assertEqualsWithDelta(0, (float) $trip->fare, 0.01);
        $this->assertSame('Pickup', $trip->direction);
        $this->assertNotNull($trip->customer_id, 'The client must get a customer record.');
    }

    public function test_the_app_endpoint_opens_the_trip_with_the_fare_the_agent_quoted(): void
    {
        $agent = $this->agent();

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson('/api/v1/agent/transport-bookings', $this->payload([
                'fare' => 3500, // the fare the agent quoted their client
            ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.transport.status', 'Pending')
            ->assertJsonPath('data.transport.direction', 'Pickup');

        $this->assertEqualsWithDelta(3500, (float) $response->json('data.transport.fare'), 0.01);
        $this->assertSame('Pending', TransportBooking::where('agent_id', $agent->id)->first()->status);
    }

    public function test_an_unpriced_request_still_opens_at_fare_zero(): void
    {
        $agent = $this->agent();

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson('/api/v1/agent/transport-bookings', $this->payload());

        $response->assertStatus(201);
        $this->assertEqualsWithDelta(0, (float) $response->json('data.transport.fare'), 0.01,
            'No quoted fare means the desk still prices it.');
    }

    public function test_an_attributed_sale_raises_no_crm_lead(): void
    {
        $agent = $this->agent();
        // APP_DEMO=true seeds real Lead rows before every test, so the count
        // to hold steady is a baseline, not zero.
        $before = Lead::count();

        $this->actingAs($agent)
            ->post(route('agent.transport.store'), $this->payload());

        $this->assertSame($before, Lead::count(),
            'An attributed sale is not a cold website enquiry and must not hit the lead board.');
    }

    public function test_a_repeated_submit_returns_the_same_trip(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent)->post(route('agent.transport.store'), $this->payload());
        $this->actingAs($agent)->post(route('agent.transport.store'), $this->payload());

        $this->assertSame(1, TransportBooking::where('agent_id', $agent->id)->count());
    }
}
