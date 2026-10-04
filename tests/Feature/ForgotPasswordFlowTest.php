<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Forgot password, driven the whole way through on both front ends.
 *
 * Each test finishes by signing in with the NEW password — the only assertion
 * that actually proves the reset worked, rather than that the screens returned
 * the right redirects.
 */
class ForgotPasswordFlowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_web_forgot_password_end_to_end(): void
    {
        Mail::fake();

        $user = User::where('email', 'agent@bugbuild.com')->firstOrFail();

        // 1. ask for a code
        $this->post(route('password.verify.email'), ['email' => $user->email])
            ->assertRedirect(route('password.tokenForm'));

        $code = (string) $user->fresh()->token;
        $this->assertSame(6, strlen($code), 'A six-digit code should have been issued.');

        // 2. enter it
        $this->post(route('password.verifyToken'), ['user_id' => $user->id, 'token' => $code])
            ->assertRedirect(route('password.resetForm'));

        // 3. set the new password
        $this->post(route('password.reset'), [
            'user_id'          => $user->id,
            'token'            => $code,
            'new_password'     => 'BrandNewPass1',
            'confirm_password' => 'BrandNewPass1',
        ])->assertRedirect();

        // 4. it actually works
        $this->assertTrue(Hash::check('BrandNewPass1', $user->fresh()->password));
        $this->post(route('login'), ['email' => $user->email, 'password' => 'BrandNewPass1'])
            ->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_web_reset_rejects_an_expired_code(): void
    {
        Mail::fake();
        $user = User::where('email', 'agent@bugbuild.com')->firstOrFail();

        $this->post(route('password.verify.email'), ['email' => $user->email]);
        $code = (string) $user->fresh()->token;

        $user->forceFill(['token_expires_at' => now()->subMinute()])->save();

        // Redirected back with an error rather than let through.
        $this->post(route('password.verifyToken'), ['user_id' => $user->id, 'token' => $code])
            ->assertRedirect();
        $this->assertFalse(session()->get('password_reset', false));
    }

    public function test_app_forgot_password_end_to_end(): void
    {
        Mail::fake();

        $customer = Customer::whereNotNull('email')->firstOrFail();

        // 1. ask for a code
        $this->postJson('/api/v1/auth/otp/request', ['email' => $customer->email])
            ->assertOk();

        $code = (string) $customer->fresh()->otp;
        $this->assertSame(6, strlen($code));

        // 2. reset with it
        $this->postJson('/api/v1/auth/reset-password', [
            'email'                 => $customer->email,
            'otp'                   => $code,
            'password'              => 'BrandNewPass1',
            'password_confirmation' => 'BrandNewPass1',
        ])->assertOk();

        // 3. the new password signs in, and the code is burnt
        $this->assertNull($customer->fresh()->otp, 'The code must not survive its use.');
        $this->postJson('/api/v1/auth/login', [
            'login'    => $customer->email,
            'password' => 'BrandNewPass1',
        ])->assertOk()->assertJsonPath('success', true);
    }

    public function test_app_reset_rejects_a_wrong_code(): void
    {
        Mail::fake();
        $customer = Customer::whereNotNull('email')->firstOrFail();
        $this->postJson('/api/v1/auth/otp/request', ['email' => $customer->email]);

        $this->postJson('/api/v1/auth/reset-password', [
            'email'                 => $customer->email,
            'otp'                   => '000000',
            'password'              => 'Whatever123',
            'password_confirmation' => 'Whatever123',
        ])->assertStatus(401);
    }
}
