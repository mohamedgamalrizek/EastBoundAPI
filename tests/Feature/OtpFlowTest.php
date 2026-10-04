<?php

namespace Tests\Feature;

use App\Mail\OtpCode;
use App\Models\Customer;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * One code mechanism for the whole system: same length, same expiry, same
 * email, whether the account is a staff user (web) or a customer (app).
 */
class OtpFlowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_a_code_is_emailed_to_a_staff_user_and_expires(): void
    {
        Mail::fake();
        $user = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        $this->assertTrue(OtpService::issue($user));
        Mail::assertSent(OtpCode::class);

        $user->refresh();
        $this->assertSame(6, strlen((string) $user->token));
        $this->assertNotNull($user->token_expires_at, 'The web code must carry an expiry.');
        $this->assertTrue(OtpService::check($user, $user->token));

        // and it stops working once it has run out
        $user->forceFill(['token_expires_at' => now()->subMinute()])->save();
        $this->assertFalse(OtpService::check($user, $user->token));
    }

    public function test_a_code_is_emailed_to_a_customer(): void
    {
        Mail::fake();
        $customer = Customer::whereNotNull('email')->firstOrFail();

        $this->assertTrue(OtpService::issue($customer));
        Mail::assertSent(OtpCode::class);

        $customer->refresh();
        $this->assertSame(6, strlen((string) $customer->otp));
        $this->assertTrue(OtpService::check($customer, $customer->otp));
    }

    public function test_a_wrong_code_and_a_cleared_code_are_both_rejected(): void
    {
        Mail::fake();
        $customer = Customer::whereNotNull('email')->firstOrFail();
        OtpService::issue($customer);
        $customer->refresh();

        $this->assertFalse(OtpService::check($customer, '000000') && $customer->otp !== '000000');

        OtpService::clear($customer);
        $customer->refresh();
        $this->assertFalse(OtpService::check($customer, '123456'));
    }

    public function test_an_account_without_an_email_cannot_be_issued_a_code(): void
    {
        Mail::fake();
        $customer = Customer::whereNotNull('email')->firstOrFail();
        $customer->forceFill(['email' => null])->save();

        $this->assertFalse(OtpService::issue($customer));
        Mail::assertNothingSent();
    }

    public function test_registration_grants_no_access_until_the_code_is_verified(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        // Registering must NOT hand back a token: the account exists but is
        // unverified. The app used to navigate straight into the dashboard on
        // the strength of this response alone.
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Unverified Person', 'phone' => '01700000999',
            'email' => 'unverified@example.com',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
            'gender' => 'male',
        ])->assertCreated();

        $data = $response->json('data');
        $this->assertArrayNotHasKey('token', $data, 'Registration must not issue a token.');
        $this->assertTrue($data['requires_otp']);
        $this->assertSame('unverified@example.com', $data['email']);
        $this->assertSame('+8801700000999', $data['phone'], 'Phones come back normalised.');

        // Verifying by email is what issues it.
        $code = \App\Models\Customer::where('email', 'unverified@example.com')->value('otp');
        $verified = $this->postJson('/api/v1/auth/otp/verify', [
            'email' => 'unverified@example.com', 'otp' => $code,
        ])->assertOk();

        $this->assertNotEmpty($verified->json('data.token'), 'Verifying must issue the token.');
    }

    public function test_app_registration_now_requires_an_email(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'No Address', 'phone' => '01700000123',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
            'gender' => 'male',
        ])->assertStatus(422);
    }
}
