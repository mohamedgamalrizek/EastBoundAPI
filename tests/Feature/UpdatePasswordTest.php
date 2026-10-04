<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Staff password change.
 *
 * Fortify's own routes are disabled (Fortify::ignoreRoutes() in
 * FortifyServiceProvider — they duplicated route names and broke route:cache),
 * so this covers the app's endpoint: PUT /password/update on
 * Backend\ProfileController.
 */
class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    /** UserFactory hashes this for every generated user. */
    private const CURRENT     = '12345678';
    private const REPLACEMENT = 'new-password-123';

    /**
     * The route is permission-gated. Grant the permission on the user directly
     * rather than seeding roles — RefreshDatabase commits the first test
     * class's seed, so re-seeding here hits unique constraints.
     */
    private function user(): User
    {
        return User::factory()->create([
            'permissions' => ['dashboard_read', 'profile_read', 'password_update'],
        ]);
    }

    public function test_password_can_be_updated(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put(route('profile.password.update'), [
            'old_password'     => self::CURRENT,
            'new_password'     => self::REPLACEMENT,
            'confirm_password' => self::REPLACEMENT,
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(self::REPLACEMENT, $user->fresh()->password));
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put(route('profile.password.update'), [
            'old_password'     => 'wrong-password',
            'new_password'     => self::REPLACEMENT,
            'confirm_password' => self::REPLACEMENT,
        ]);

        $this->assertTrue(Hash::check(self::CURRENT, $user->fresh()->password));
    }

    public function test_new_passwords_must_match(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put(route('profile.password.update'), [
            'old_password'     => self::CURRENT,
            'new_password'     => self::REPLACEMENT,
            'confirm_password' => 'something-else-entirely',
        ])->assertSessionHasErrors('confirm_password');

        $this->assertTrue(Hash::check(self::CURRENT, $user->fresh()->password));
    }
}
