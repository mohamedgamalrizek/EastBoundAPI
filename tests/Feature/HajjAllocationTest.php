<?php

namespace Tests\Feature;

use App\Models\HajjFlight;
use App\Models\HajjGroup;
use App\Models\HajjPilgrim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hajj groups and flight allocation.
 *
 * Both screens used to be free-text boxes: the office retyped "Group A" and
 * "12A" on every row, a typo made a second group nobody noticed, and two
 * pilgrims could be given the same seat. These tests hold the two rules that
 * replaced the typing — the lists are real records, and a seat belongs to one
 * person.
 */
class HajjAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
    }

    public function test_the_allocation_screens_render(): void
    {
        foreach (['hajj.groups', 'hajj.flight', 'hajj.hotel'] as $name) {
            $response = $this->actingAs($this->admin())->get(route($name));
            $this->assertSame(200, $response->status(), "GET {$name} returned {$response->status()}");
        }
    }

    public function test_a_group_can_be_created_empty_and_appears_in_the_list(): void
    {
        $this->actingAs($this->admin())
            ->post(route('hajj.group.store'), ['name' => 'Group Z', 'leader' => 'Imam Sahib'])
            ->assertRedirect();

        $group = HajjGroup::where('name', 'Group Z')->first();

        $this->assertNotNull($group);
        $this->assertSame('Imam Sahib', $group->leader);

        // Nobody is in it yet, and it is still offered as a destination.
        $this->assertSame(0, $group->pilgrims()->count());
        $this->assertContains('Group Z', HajjGroup::names()->all());

        $this->actingAs($this->admin())->get(route('hajj.groups'))->assertSee('Group Z');
    }

    public function test_a_duplicate_group_name_is_refused(): void
    {
        HajjGroup::create(['name' => 'Group Y']);

        $this->actingAs($this->admin())
            ->post(route('hajj.group.store'), ['name' => 'Group Y'])
            ->assertRedirect();

        $this->assertSame(1, HajjGroup::where('name', 'Group Y')->count());
    }

    public function test_a_group_with_pilgrims_cannot_be_deleted(): void
    {
        $group   = HajjGroup::create(['name' => 'Group W']);
        $pilgrim = HajjPilgrim::first();
        $pilgrim->update(['group_name' => 'Group W']);

        // Group deletes respond with JSON (matching the site-wide AJAX-delete
        // convention), not a redirect.
        $this->actingAs($this->admin())
            ->delete(route('hajj.group.delete', $group->id))
            ->assertStatus(400)
            ->assertJsonPath('status', false);

        $this->assertNotNull($group->fresh(), 'A filled group must survive the delete.');

        // Emptying it first makes the delete work.
        $pilgrim->update(['group_name' => null]);

        $this->actingAs($this->admin())
            ->delete(route('hajj.group.delete', $group->id))
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertNull($group->fresh());
    }

    public function test_a_flight_seat_map_is_generated_from_rows_and_letters(): void
    {
        $flight = HajjFlight::create([
            'flight_no'    => 'BG-9999',
            'seat_rows'    => 3,
            'seat_letters' => 'ABC',
        ]);

        $this->assertSame(9, $flight->seatCount());
        $this->assertSame(['1A', '1B', '1C', '2A', '2B', '2C', '3A', '3B', '3C'], $flight->seats());
    }

    public function test_two_pilgrims_cannot_hold_the_same_seat(): void
    {
        // BG-1011 is one of the seeded demo flights; take it as it comes.
        HajjFlight::firstOrCreate(
            ['flight_no' => 'BG-1011'],
            ['seat_rows' => 30, 'seat_letters' => 'ABCDEF']
        );

        [$first, $second] = HajjPilgrim::take(2)->get()->all();

        $this->actingAs($this->admin())
            ->put(route('hajj.allocate', $first->id), ['flight_no' => 'BG-1011', 'seat_no' => '12A'])
            ->assertRedirect();

        $this->assertSame('12A', $first->fresh()->seat_no);

        $this->actingAs($this->admin())
            ->put(route('hajj.allocate', $second->id), ['flight_no' => 'BG-1011', 'seat_no' => '12A'])
            ->assertRedirect();

        $this->assertNull($second->fresh()->seat_no, 'The second pilgrim must not get a taken seat.');

        // The same pilgrim re-saving their own seat is not a clash.
        $this->actingAs($this->admin())
            ->put(route('hajj.allocate', $first->id), ['flight_no' => 'BG-1011', 'seat_no' => '12A'])
            ->assertRedirect();

        $this->assertSame('12A', $first->fresh()->seat_no);
    }

    public function test_a_flight_number_arriving_without_the_picker_is_registered(): void
    {
        $pilgrim = HajjPilgrim::first();

        $this->actingAs($this->admin())
            ->put(route('hajj.allocate', $pilgrim->id), ['flight_no' => 'SV-3801'])
            ->assertRedirect();

        // Otherwise the flight-allocation screen would show a pilgrim on a
        // flight that is not in its own dropdown.
        $this->assertNotNull(HajjFlight::where('flight_no', 'SV-3801')->first());
    }
}
