<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Phones are stored E.164, whatever shape the form sends.
 *
 * The point is that one person is one account and can always sign back in:
 * before this, `01711…` and `+88011711…` were different strings, so they
 * passed the unique rule separately and only matched a login typed the same
 * way they were registered.
 */
class PhoneRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function register(string $phone, string $email, ?string $dial = null): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/v1/auth/register', array_filter([
            'name' => 'Phone Person', 'phone' => $phone, 'email' => $email,
            'dial_code' => $dial,
            'password' => 'secret123', 'password_confirmation' => 'secret123',
            'gender' => 'male',
        ]));
    }

    public function test_a_national_number_is_stored_in_e164(): void
    {
        Mail::fake();

        $this->register('01711000111', 'e164@example.com')->assertCreated();

        $this->assertSame('+8801711000111', Customer::where('email', 'e164@example.com')->value('phone'));
    }

    public function test_the_same_number_in_another_shape_is_rejected_as_taken(): void
    {
        Mail::fake();

        $this->register('01711000222', 'first@example.com')->assertCreated();

        // Same human, different spelling — must not become a second account.
        $this->register('+8801711000222', 'second@example.com')
            ->assertStatus(422)
            ->assertJsonPath('data.phone.0', fn ($m) => $m !== null);

        $this->assertSame(1, Customer::where('phone', '+8801711000222')->count());
    }

    public function test_login_works_whatever_shape_the_number_is_typed_in(): void
    {
        Mail::fake();
        $this->register('01711000333', 'login@example.com')->assertCreated();

        $customer = Customer::where('email', 'login@example.com')->firstOrFail();
        $customer->forceFill(['otp' => null, 'otp_expires_at' => null])->save();

        foreach (['01711000333', '+8801711000333', '8801711000333'] as $typed) {
            $this->postJson('/api/v1/auth/login', ['login' => $typed, 'password' => 'secret123'])
                ->assertOk();
        }
    }

    public function test_a_foreign_number_keeps_its_own_country(): void
    {
        Mail::fake();

        $this->register('7911123456', 'uk@example.com', '44')->assertCreated();

        $this->assertSame('+447911123456', Customer::where('email', 'uk@example.com')->value('phone'));
    }
}
