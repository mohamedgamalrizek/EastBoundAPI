<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Staff password reset.
 *
 * The app does not use Fortify's reset UI — Auth\PasswordController owns a
 * three-step flow (email → emailed numeric token → new password). Fortify's
 * competing routes are disabled via Fortify::ignoreRoutes(), so this is the
 * only reset flow the app exposes.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_form_can_be_rendered(): void
    {
        $this->get(route('password.forgotForm'))->assertStatus(200);
    }

    public function test_requesting_a_reset_issues_a_token(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post(route('password.verify.email'), ['email' => $user->email])
            ->assertRedirect(route('password.tokenForm'));

        $this->assertNotNull($user->fresh()->token, 'no reset token was issued');
        $this->assertSame($user->id, session('user_id'));
    }

    public function test_an_unknown_email_is_rejected(): void
    {
        Mail::fake();

        $this->post(route('password.verify.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_the_token_screen_is_not_reachable_without_a_request(): void
    {
        $this->get(route('password.tokenForm'))->assertRedirect('/');
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post(route('password.verify.email'), ['email' => $user->email]);
        $token = $user->fresh()->token;

        $this->post(route('password.verifyToken'), ['user_id' => $user->id, 'token' => $token])
            ->assertRedirect(route('password.resetForm'));

        $this->get(route('password.resetForm'))->assertStatus(200);

        // Posted by path, not by name: Fortify registers a GET route that also
        // claims the "password.reset" name, so route() would resolve to that.
        $this->post(url('password/reset'), [
            'user_id'          => $user->id,
            'token'            => $token,
            'new_password'     => 'reset-password-123',
            'confirm_password' => 'reset-password-123',
        ]);

        $this->assertTrue(Hash::check('reset-password-123', $user->fresh()->password));
    }

    public function test_a_wrong_token_does_not_reset_the_password(): void
    {
        Mail::fake();

        $user     = User::factory()->create();
        $original = $user->password;

        $this->post(route('password.verify.email'), ['email' => $user->email]);

        $this->post(route('password.verifyToken'), ['user_id' => $user->id, 'token' => '00000'])
            ->assertSessionHasErrors('token');

        $this->assertSame($original, $user->fresh()->password);
    }
}
