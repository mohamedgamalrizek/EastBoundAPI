<?php

namespace Tests\Feature;

use App\Models\Backend\Setting;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Refund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * BookingController@cancel used to let a customer cancel any paid booking at
 * any time — including after the travel date — for a full wallet refund.
 *
 * These tests pin the admin-configurable replacement (see
 * App\Services\Booking\CancellationPolicy): a booking too close to (or past)
 * its travel date cannot be cancelled online, and a paid booking that *can*
 * be cancelled is refunded net of the cancellation penalty.
 */
class BookingCancellationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function setPolicy(int $windowHours, int $penaltyPercent): void
    {
        Setting::updateOrCreate(['key' => 'booking_cancellation_window_hours'], ['value' => (string) $windowHours]);
        Setting::updateOrCreate(['key' => 'booking_cancellation_penalty_percent'], ['value' => (string) $penaltyPercent]);

        // settings() is cached forever; the seeded defaults are already
        // cached by the time this test runs.
        Cache::forget(tenant_cache_prefix() . 'settings');
    }

    private function customer(): Customer
    {
        return Customer::create([
            'name'   => 'Test Customer',
            'email'  => 'cancel-policy-' . uniqid() . '@example.com',
            'phone'  => '01700000000',
            'status' => 'active',
        ]);
    }

    /**
     * A 'paid' booking's invoice/receipt is created by BookingObserver
     * (BillingService::syncBooking) as soon as it is saved paid, so the
     * booking's full amount is already the refundable paid amount.
     */
    private function paidBooking(Customer $customer, string $travelDate, float $amount = 1000): Booking
    {
        return Booking::create([
            'customer_id'    => $customer->id,
            'customer_name'  => $customer->name,
            'customer_email' => $customer->email,
            'travel_date'    => $travelDate,
            'travelers'      => 1,
            'amount'         => $amount,
            'status'         => 'paid',
            'payment_method' => 'Cash',
        ]);
    }

    public function test_cancelling_inside_the_window_is_rejected(): void
    {
        // travel_date only stores a date (no time-of-day), so "tomorrow" is
        // somewhere between 0 and 24 hours away depending on what time the
        // test runs. A 48-hour window makes it reliably "inside" either way.
        $this->setPolicy(windowHours: 48, penaltyPercent: 10);

        $customer = $this->customer();
        $booking  = $this->paidBooking($customer, now()->addDay()->toDateString());

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonPath('data.cancellation_window_hours', 48);
        $this->assertStringContainsString('48', $response->json('message'));

        $this->assertSame('paid', $booking->fresh()->status);
        $this->assertSame(0, Refund::where('booking_id', $booking->id)->count());
    }

    public function test_cancelling_outside_the_window_refunds_the_amount_minus_the_penalty(): void
    {
        $this->setPolicy(windowHours: 24, penaltyPercent: 10);

        $customer = $this->customer();
        // Ten days away: comfortably outside the 24-hour window regardless of
        // what time of day the test runs.
        $booking  = $this->paidBooking($customer, now()->addDays(10)->toDateString(), amount: 1000);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertOk()->assertJsonPath('data.refunded', true);
        $this->assertEqualsWithDelta(900.0, (float) $response->json('data.refunded_amount'), 0.01);
        $this->assertEqualsWithDelta(100.0, (float) $response->json('data.penalty_amount'), 0.01);
        $this->assertSame(10, $response->json('data.penalty_percent'));

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertEqualsWithDelta(100.0, (float) $booking->cancellation_fee, 0.01);

        $refund = Refund::where('booking_id', $booking->id)->firstOrFail();
        $this->assertEqualsWithDelta(900.0, (float) $refund->amount, 0.01);
        $this->assertSame('Wallet', $refund->method);
    }

    public function test_cancelling_after_the_travel_date_is_rejected(): void
    {
        $this->setPolicy(windowHours: 24, penaltyPercent: 10);

        $customer = $this->customer();
        $booking  = $this->paidBooking($customer, now()->subDays(2)->toDateString());

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertStatus(422);
        $this->assertStringContainsString('passed', $response->json('message'));

        $this->assertSame('paid', $booking->fresh()->status);
        $this->assertSame(0, Refund::where('booking_id', $booking->id)->count());
    }

    public function test_a_zero_percent_penalty_still_refunds_in_full(): void
    {
        $this->setPolicy(windowHours: 24, penaltyPercent: 0);

        $customer = $this->customer();
        $booking  = $this->paidBooking($customer, now()->addDays(10)->toDateString(), amount: 500);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertOk();
        $this->assertEqualsWithDelta(500.0, (float) $response->json('data.refunded_amount'), 0.01);
        $this->assertEqualsWithDelta(0.0, (float) $response->json('data.penalty_amount'), 0.01);
    }
}
