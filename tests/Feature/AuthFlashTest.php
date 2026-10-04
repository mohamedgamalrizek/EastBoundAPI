<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The auth screens must SHOW what happened.
 *
 * They redirect back with a flash on failure, and the layout used to discard
 * it — so a forgot-password attempt that could not send its email looked
 * identical to the button doing nothing at all.
 */
class AuthFlashTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_a_failed_send_is_reported_on_screen(): void
    {
        // No Mail::fake(): the seeded SMTP host does not resolve, so the send
        // genuinely fails — the exact situation the user hits.
        $user = User::where('email', 'agent@bugbuild.com')->firstOrFail();

        $response = $this->followingRedirects()
            ->from(route('password.forgotForm'))
            ->post(route('password.verify.email'), ['email' => $user->email]);

        $response->assertOk();
        $response->assertSee('auth-flash', false);
        $response->assertSee(___('alert.code_email_failed'));
    }

    public function test_a_successful_send_is_reported_on_screen(): void
    {
        Mail::fake();
        $user = User::where('email', 'agent@bugbuild.com')->firstOrFail();

        $response = $this->followingRedirects()
            ->from(route('password.forgotForm'))
            ->post(route('password.verify.email'), ['email' => $user->email]);

        $response->assertOk();
        $response->assertSee(___('alert.otp_mail_send'));
    }

    public function test_an_unknown_email_comes_back_with_a_field_error(): void
    {
        // The field-level @error was already there; what matters is that the
        // request bounces with an error rather than silently doing nothing.
        $this->from(route('password.forgotForm'))
            ->post(route('password.verify.email'), ['email' => 'nobody@example.com'])
            ->assertRedirect(route('password.forgotForm'))
            ->assertSessionHasErrors('email');
    }
}
