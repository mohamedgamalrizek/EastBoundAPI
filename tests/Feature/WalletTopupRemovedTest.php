<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The wallet top-up endpoint let any authenticated customer mint their own
 * wallet balance — no gateway, no payment, just a credit up to 1,000,000 per
 * call — and then spend it on real bookings. Removed rather than wired to a
 * gateway: see WalletController (topup() deleted) and routes/api.php.
 */
class WalletTopupRemovedTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): Customer
    {
        return Customer::create([
            'name'     => 'Rafiul Islam',
            'phone'    => '01700000001',
            'email'    => 'rafiul@example.com',
            'password' => 'secret123',
            'status'   => 'active',
        ]);
    }

    public function test_the_topup_route_no_longer_exists_for_an_authenticated_customer(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/wallet/topup', ['amount' => 1000000])
            ->assertStatus(404);
    }

    public function test_the_topup_route_is_gone_even_for_a_visitor(): void
    {
        $this->postJson('/api/v1/wallet/topup', ['amount' => 500])
            ->assertStatus(404);
    }

    public function test_the_wallet_read_route_still_works(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/wallet')
            ->assertOk()
            ->assertJsonPath('data.balance', 0)
            ->assertJsonPath('data.transactions', []);
    }
}
