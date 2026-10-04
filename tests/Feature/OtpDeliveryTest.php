<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Mail\OtpCode;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Mobile OTP delivery.
 *
 * Codes are emailed — the same OtpCode message the web signup and password
 * reset use — so an agency needs SMTP and not a paid SMS gateway before anyone
 * can register. This used to go out over MiM SMS.
 *
 * The security property these tests exist for is unchanged: the code lives in
 * the database and in the email, and nowhere else. It must never come back in
 * an API response, however convenient that is during development.
 */
class OtpDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(string $phone, ?string $email = null): Customer
    {
        return Customer::create([
            'name'     => 'Test Customer',
            'phone'    => $phone,
            'email'    => $email ?? $phone . '@example.com',
            'password' => 'secret123', // hashed via model cast
            'status'   => 'active',
        ]);
    }

    public function test_registration_never_returns_the_otp(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Rafiq Islam',
            'phone'                 => '01711000111',
            'email'                 => 'rafiq@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'gender'                => Gender::MALE->value,
        ]);

        $response->assertCreated();
        $this->assertStringNotContainsString('dev_otp', $response->getContent());

        // Stored E.164 now: the column is unique and doubles as a login
        // identifier, so it is normalised on the way in.
        $otp = Customer::where('phone', '+8801711000111')->value('otp');
        $this->assertNotNull($otp, 'The OTP should still be stored for verification.');
        $this->assertStringNotContainsString($otp, $response->getContent());

        Mail::assertSent(OtpCode::class, fn ($mail) => $mail->hasTo('rafiq@example.com'));
    }

    public function test_otp_request_emails_the_code_and_hides_it_from_the_response(): void
    {
        Mail::fake();

        $customer = $this->makeCustomer('01711000222');

        $response = $this->postJson('/api/v1/auth/otp/request', ['phone' => '01711000222']);
        $response->assertOk();

        $otp = $customer->fresh()->otp;
        $this->assertNotNull($otp);
        $this->assertStringNotContainsString($otp, $response->getContent());

        // It went out by email instead, carrying that exact code.
        Mail::assertSent(OtpCode::class, function (OtpCode $mail) use ($customer, $otp) {
            return $mail->hasTo($customer->email) && $mail->code === $otp;
        });
    }

    public function test_otp_request_fails_loudly_when_the_account_has_no_email(): void
    {
        Mail::fake();

        $customer = $this->makeCustomer('01711000333');
        $customer->forceFill(['email' => null])->save();

        $this->postJson('/api/v1/auth/otp/request', ['phone' => '01711000333'])
            ->assertStatus(503);

        Mail::assertNothingSent();
    }
}
