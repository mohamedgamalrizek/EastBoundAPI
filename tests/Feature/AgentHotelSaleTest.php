<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 02 — an agent sells a hotel stay to a client, from the portal form or
 * the app, and the stay opens with the price quoted server-side.
 *
 * The rules worth holding on to: the agent never sends an amount (nights × the
 * room's published rate is quoted by CreateAgentHotelBooking), the client gets
 * a customer record, and a repeated submit returns the stay already open
 * rather than a second one.
 */
class AgentHotelSaleTest extends TestCase
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

    private function room(): HotelRoom
    {
        $hotel = Hotel::create([
            'name'            => 'Agent Test Hotel',
            'city'            => 'Dhaka',
            'country'         => 'Bangladesh',
            'category'        => 4,
            'rooms_count'     => 10,
            'price_per_night' => 10000,
            'status'          => 'active',
        ]);

        return HotelRoom::create([
            'hotel_id'        => $hotel->id,
            'room_type'       => 'Deluxe',
            'capacity'        => 2,
            'rate_per_night'  => 10000,
            'total_rooms'     => 5,
            'available_rooms' => 5,
        ]);
    }

    private function payload(HotelRoom $room, array $overrides = []): array
    {
        return array_merge([
            'hotel_id'      => $room->hotel_id,
            'hotel_room_id' => $room->id,
            'client_name'   => 'Sadia Akter',
            'client_phone'  => '01712340000',
            'check_in'      => now()->addDays(2)->toDateString(),
            'check_out'     => now()->addDays(5)->toDateString(),
        ], $overrides);
    }

    public function test_the_portal_hotel_form_renders_and_a_stay_is_created(): void
    {
        $agent = $this->agent();
        $room  = $this->room();

        $this->actingAs($agent)->get(route('agent.hotel.create'))->assertOk();

        $this->actingAs($agent)
            ->post(route('agent.hotel.store'), $this->payload($room))
            ->assertRedirect(route('agent.bookings'));

        $stay = HotelBooking::where('agent_id', $agent->id)->firstOrFail();
        $this->assertSame(3, $stay->nights);
        $this->assertEqualsWithDelta(3 * (float) $room->rate_per_night, (float) $stay->amount, 0.01);
        $this->assertSame('Booked', $stay->status);
        $this->assertNotNull($stay->customer_id, 'The client must get a customer record.');
    }

    public function test_the_app_endpoint_quotes_the_price_and_ignores_a_forged_amount(): void
    {
        $agent = $this->agent();
        $room  = $this->room();

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson('/api/v1/agent/hotel-bookings', $this->payload($room, [
                'check_out' => now()->addDays(4)->toDateString(),
                'amount'    => 1, // a client-side number the server must ignore
            ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.booking.nights', 2)
            ->assertJsonPath('data.booking.status', 'Booked');

        // Priced from the room rate, not from the forged amount.
        $this->assertEqualsWithDelta(
            2 * (float) $room->rate_per_night,
            (float) $response->json('data.booking.amount'),
            0.01
        );
    }

    public function test_a_repeated_submit_returns_the_same_stay(): void
    {
        $agent = $this->agent();
        $room  = $this->room();

        $this->actingAs($agent)->post(route('agent.hotel.store'), $this->payload($room));
        $this->actingAs($agent)->post(route('agent.hotel.store'), $this->payload($room));

        $this->assertSame(1, HotelBooking::where('agent_id', $agent->id)->count());
    }
}
