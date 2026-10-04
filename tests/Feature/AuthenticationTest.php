<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Staff login. The app uses Auth\AuthController rather than Fortify's login,
 * and sends each user to the panel their role can open (User::home()), so this
 * asserts the session and that redirect rather than one fixed URL.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** UserFactory hashes this for every generated user. */
    private const FACTORY_PASSWORD = '12345678';

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => self::FACTORY_PASSWORD,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect($user->fresh()->home());
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
