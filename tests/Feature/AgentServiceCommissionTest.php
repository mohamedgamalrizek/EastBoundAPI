<?php

namespace Tests\Feature;

use App\Models\AgentCommission;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Models\Role;
use App\Models\TransportBooking;
use App\Models\User;
use App\Services\Accounting\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 01 — any sale that carries an agent_id can earn that agent a commission.
 *
 * Until now agent_commissions could only point at tour bookings (booking_id);
 * a hotel stay or transport trip sold by an agent had nowhere to record who
 * sold it. These tests hold the new rule: a paid, agent-assigned stay or trip
 * creates exactly one commission, keyed by its own morph, and the books stay
 * balanced through a rebuild.
 */
class AgentServiceCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function agent(float $rate = 10.0): User
    {
        $role = Role::where('name', 'Agent')->first()
            ?? Role::create(['name' => 'Agent', 'slug' => 'agent']);

        return User::factory()->create([
            'role_id'         => $role->id,
            'commission_rate' => $rate,
        ]);
    }

    private function customer(string $phone = '01712345678'): Customer
    {
        return Customer::create([
            'name'     => 'Rahim Uddin',
            'phone'    => $phone,
            'email'    => $phone . '@example.com',
            'password' => 'secret123',
            'status'   => 'active',
        ]);
    }

    private function room(): HotelRoom
    {
        $hotel = Hotel::create([
            'name'            => 'Test Hotel',
            'city'            => 'Dhaka',
            'country'         => 'Bangladesh',
            'category'        => 4,
            'rooms_count'     => 10,
            'price_per_night' => 10000,
            'status'          => 'Active',
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

    private function stay(?User $agent, string $status = 'Paid'): HotelBooking
    {
        $room = $this->room();

        return HotelBooking::create([
            'booking_no'     => 'HTL-AG-' . uniqid(),
            'hotel_id'       => $room->hotel_id,
            'hotel_room_id'  => $room->id,
            'customer_id'    => $this->customer()->id,
            'agent_id'       => $agent?->id,
            'guest_name'     => 'Rahim Uddin',
            'check_in'       => now()->addDays(2)->toDateString(),
            'check_out'      => now()->addDays(4)->toDateString(),
            'nights'         => 2,
            'amount'         => 20000,
            'status'         => $status,
            'payment_method' => 'Cash',
        ]);
    }

    public function test_a_paid_hotel_stay_assigned_to_an_agent_earns_a_commission(): void
    {
        $stay = $this->stay($this->agent(10));

        $commission = AgentCommission::where('source_type', (new HotelBooking)->getMorphClass())
            ->where('source_id', $stay->id)
            ->where('is_auto', true)
            ->first();

        $this->assertNotNull($commission, 'A paid agent stay must earn a commission.');
        $this->assertSame($stay->agent_id, $commission->agent_id);
        $this->assertSame(2000.0, (float) $commission->amount);   // 10% of 20,000
        $this->assertSame(10.0, (float) $commission->rate);
        $this->assertSame($stay->booking_no, $commission->booking_ref);
        $this->assertStringStartsWith('COM-H', $commission->reference);
        $this->assertNull($commission->booking_id, 'Service commissions are not tour bookings.');

        // The expense reaches the books as soon as it is earned.
        $this->assertSame(1, $commission->journalEntries()->count());
    }

    public function test_resaving_a_paid_stay_does_not_duplicate_the_commission(): void
    {
        $stay = $this->stay($this->agent(10));
        $this->assertSame(1, $this->commissionCount($stay));

        $stay->update(['amount' => 24000]);

        $this->assertSame(1, $this->commissionCount($stay));
        $commission = $this->commissionFor($stay);
        $this->assertEqualsWithDelta(2400, (float) $commission->amount, 0.01);
    }

    public function test_an_unpaid_or_unassigned_stay_earns_nothing(): void
    {
        $confirmed = $this->stay($this->agent(10), 'Confirmed');
        $unassigned = $this->stay(null, 'Paid');

        $this->assertSame(0, $this->commissionCount($confirmed));
        $this->assertSame(0, $this->commissionCount($unassigned));
    }

    public function test_unassigning_the_agent_withdraws_the_pending_commission(): void
    {
        $stay = $this->stay($this->agent(10));
        $this->assertSame(1, $this->commissionCount($stay));

        // The desk decides the stay was a direct booking after all.
        $stay->update(['agent_id' => null]);

        $this->assertSame(0, $this->commissionCount($stay),
            'A pending commission follows its sale out when the sale stops qualifying.');
    }

    public function test_a_paid_transport_trip_assigned_to_an_agent_earns_a_commission(): void
    {
        $agent = $this->agent(7.5);

        $trip = TransportBooking::create([
            'customer_id'    => $this->customer()->id,
            'agent_id'       => $agent->id,
            'booking_no'     => 'TR-AG-' . uniqid(),
            'type'           => 'Bus',
            'customer_name'  => 'Rahim Uddin',
            'route'          => 'Dhaka → Chattogram',
            'travel_date'    => now()->addWeek()->toDateString(),
            'fare'           => 8000,
            'status'         => 'Paid',
            'payment_method' => 'Cash',
        ]);

        $commission = AgentCommission::where('source_type', (new TransportBooking)->getMorphClass())
            ->where('source_id', $trip->id)
            ->where('is_auto', true)
            ->first();

        $this->assertNotNull($commission, 'A paid agent trip must earn a commission.');
        $this->assertEqualsWithDelta(600, (float) $commission->amount, 0.01);   // 7.5% of 8,000
        $this->assertSame($trip->booking_no, $commission->booking_ref);
        $this->assertStringStartsWith('COM-T', $commission->reference);

        // A trip still awaiting a fare earns nothing yet.
        $trip->update(['status' => 'Pending', 'fare' => 0]);
        $this->assertSame(0, $this->commissionCount($trip));
    }

    public function test_tour_bookings_still_earn_commissions_and_rebuild_balances(): void
    {
        $agent = $this->agent(10);
        $customer = $this->customer();

        $booking = Booking::create([
            'customer_id'    => $customer->id,
            'customer_name'  => $customer->name,
            'booking_no'     => 'BKG-AG-' . uniqid(),
            'agent_id'       => $agent->id,
            'status'         => 'paid',
            'payment_method' => 'Cash',
            'amount'         => 50000,
            'travel_date'    => now()->addMonth()->toDateString(),
        ]);

        $commission = AgentCommission::where('source_type', (new Booking)->getMorphClass())
            ->where('source_id', $booking->id)
            ->where('is_auto', true)
            ->first();

        // The legacy booking link is kept populated for tour commissions.
        $this->assertNotNull($commission);
        $this->assertSame($booking->id, $commission->booking_id);
        $this->assertEqualsWithDelta(5000, (float) $commission->amount, 0.01);

        // The whole ledger rebuilds from its documents and still balances.
        $ledger = app(LedgerService::class);
        $ledger->rebuildAll();

        $debits  = array_sum($ledger->legTotals('debit'));
        $credits = array_sum($ledger->legTotals('credit'));

        $this->assertEqualsWithDelta($credits, $debits, 0.01, 'Rebuild must leave a balanced ledger.');
        $this->assertSame(0, DB::table('account_transactions')->whereNull('contra_account_id')->count());
    }

    private function commissionFor($source): ?AgentCommission
    {
        return AgentCommission::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->where('is_auto', true)
            ->first();
    }

    private function commissionCount($source): int
    {
        return AgentCommission::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->where('is_auto', true)
            ->count();
    }
}
