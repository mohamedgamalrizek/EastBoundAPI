<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The website signup must produce the same E.164 value the app does — one
 * customer, one number, whichever front end they arrived through.
 */
class WebPhoneSignupTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_the_form_renders_a_country_picker(): void
    {
        $response = $this->get(route('registerForm'))->assertOk();

        $response->assertSee('name="dial_code"', false);
        $response->assertSee('BD +880', false);
        $response->assertSee('GB +44', false);
    }

    public function test_a_web_signup_is_stored_in_e164(): void
    {
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'Web Person', 'email' => 'web@example.com',
            'dial_code' => '880', 'phone' => '01711000444',
            'password' => 'secret123', 'confirm_password' => 'secret123',
            'gender' => 'male', 'date_of_birth' => '1995-05-20',
        ]);

        $this->assertSame('+8801711000444', User::where('email', 'web@example.com')->value('phone'));
    }

    public function test_the_website_and_the_app_agree_on_the_same_number(): void
    {
        Mail::fake();

        // app
        $this->postJson('/api/v1/auth/register', [
            'name' => 'App Person', 'email' => 'app@example.com',
            'dial_code' => '44', 'phone' => '07911123456',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
            'gender' => 'male',
        ])->assertCreated();

        // website, same human typing it the international way
        $this->post(route('register'), [
            'name' => 'Web Person', 'email' => 'web2@example.com',
            'dial_code' => '44', 'phone' => '+447911123456',
            'password' => 'secret123', 'confirm_password' => 'secret123',
            'gender' => 'male', 'date_of_birth' => '1995-05-20',
        ]);

        $this->assertSame(
            Customer::where('email', 'app@example.com')->value('phone'),
            User::where('email', 'web2@example.com')->value('phone'),
            'Both front ends must normalise to the same string.'
        );
    }
}
