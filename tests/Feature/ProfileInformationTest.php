<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Staff profile update.
 *
 * Fortify's routes are disabled (Fortify::ignoreRoutes()), so this covers the
 * app's own endpoint: PUT /profile/update on Backend\ProfileController.
 * Email is not editable there — the profile form owns name, DOB, gender,
 * address and about.
 */
class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        // Permission granted on the user directly: RefreshDatabase commits the
        // first test class's seed, so re-seeding here would violate uniques.
        $user = User::factory()->create([
            'permissions' => ['dashboard_read', 'profile_read', 'profile_update'],
        ]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name'          => 'Test Name',
            'date_of_birth' => '1990-01-01',
            'gender'        => Gender::MALE->value,
            'address'       => 'House 42, Road 11, Gulshan-1, Dhaka',
            'about'         => 'Updated from the profile form.',
        ])->assertSessionHasNoErrors();

        $fresh = $user->fresh();
        $this->assertSame('Test Name', $fresh->name);
        $this->assertSame('House 42, Road 11, Gulshan-1, Dhaka', $fresh->address);
    }
}
