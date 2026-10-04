<?php

namespace Tests\Feature;

use App\Models\Backend\Setting;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentIntent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Online payment through SSLCOMMERZ.
 *
 * The properties worth holding on to are all about money moving exactly once:
 * an unconfirmed booking is never settled by the callback alone, a repeated
 * callback does not receipt twice, and a tampered amount is refused.
 */
class OnlinePaymentTest extends TestCase
{
    use RefreshDatabase;

    private const STORE = 'flow_demo';

    private function enableSslcommerz(): void
    {
        foreach ([
            'sslcommerz_store_id'     => self::STORE,
            'sslcommerz_store_passwd' => 'demo-pass',
            'sslcommerz_sandbox'      => '1',
            'sslcommerz_status'       => '1',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(tenant_cache_prefix() . 'settings');
    }

    private function customer(): Customer
    {
        return Customer::create([
            'name'     => 'Nusrat Jahan',
            'phone'    => '01712345678',
            'email'    => 'nusrat@example.com',
            'password' => 'secret123',
            'status'   => 'active',
        ]);
    }

    private function booking(Customer $customer, float $amount = 25000): Booking
    {
        return Booking::create([
            'customer_id'   => $customer->id,
            'customer_name' => $customer->name,
            'booking_no'    => 'BKG-TEST-1',
            'status'        => 'confirmed',
            'amount'        => $amount,
            'travel_date'   => now()->addMonth()->toDateString(),
        ]);
    }

    /** The provider's init call succeeds and hands back a checkout URL. */
    private function fakeInitSuccess(): void
    {
        Http::fake([
            'sandbox.sslcommerz.com/gwprocess/*' => Http::response([
                'status'         => 'SUCCESS',
                'sessionkey'     => 'SESSION123',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/testcde123',
            ], 200),
        ]);
    }

    /** The validation API agrees the money arrived, for $amount. */
    private function fakeValidation(string $reference, float $amount): void
    {
        Http::fake([
            'sandbox.sslcommerz.com/validator/*' => Http::response([
                'status'       => 'VALID',
                'tran_id'      => $reference,
                'amount'       => $amount,
                'currency'     => 'BDT',
                'bank_tran_id' => 'BANKTRX999',
            ], 200),
            'sandbox.sslcommerz.com/gwprocess/*' => Http::response([
                'status'         => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/testcde123',
            ], 200),
        ]);
    }

    /**
     * The provider's callback arrives as its own request — the payer's app
     * session has nothing to do with it. Guards are forgotten first so the
     * result page renders as a visitor would see it.
     */
    private function providerCallback(string $reference, array $payload = ['status' => 'VALID', 'val_id' => 'VAL123'])
    {
        $this->app['auth']->forgetGuards();

        return $this->post("/payment/callback/sslcommerz/{$reference}", $payload);
    }

    public function test_paying_with_a_live_gateway_returns_a_redirect_and_settles_nothing_yet(): void
    {
        $this->enableSslcommerz();
        $this->fakeInitSuccess();

        $customer = $this->customer();
        $booking  = $this->booking($customer);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/pay", ['method' => 'sslcommerz']);

        $response->assertOk()
            ->assertJsonPath('data.online', true)
            ->assertJsonPath('data.gateway', 'sslcommerz');

        $this->assertStringStartsWith('https://sandbox.sslcommerz.com/', $response->json('data.redirect_url'));

        // Nothing is paid until the provider says so.
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame(PaymentIntent::STATUS_PENDING, PaymentIntent::first()->status);
        // Scoped to this booking: the suite's seeded demo data has paid
        // invoices of its own.
        $this->assertSame(0, Invoice::where('booking_id', $booking->id)->where('paid_amount', '>', 0)->count());
    }

    public function test_a_validated_callback_settles_the_booking_once(): void
    {
        $this->enableSslcommerz();
        $this->fakeInitSuccess();

        $customer = $this->customer();
        $booking  = $this->booking($customer, 25000);

        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/pay", ['method' => 'sslcommerz'])
            ->assertOk();

        $intent = PaymentIntent::first();
        $this->fakeValidation($intent->reference, 25000);

        $this->providerCallback($intent->reference)->assertOk();

        $intent->refresh();
        $this->assertTrue($intent->isPaid());
        $this->assertSame('BANKTRX999', $intent->gateway_ref);
        $this->assertSame('paid', $booking->fresh()->status);

        // The ordinary billing path ran: one invoice, fully receipted, in the
        // gateway's method.
        $invoice = Invoice::where('booking_id', $booking->id)->firstOrFail();
        $this->assertEqualsWithDelta(25000, (float) $invoice->paid_amount, 0.01);
        $this->assertSame(1, $invoice->receipts()->count());
        $this->assertSame('Card', $invoice->receipts()->first()->method);

        // The provider repeating its callback (browser return + IPN) must not
        // receipt the booking a second time.
        $this->providerCallback($intent->reference)->assertOk();

        $this->assertSame(1, $invoice->fresh()->receipts()->count());
        $this->assertEqualsWithDelta(25000, (float) $invoice->fresh()->paid_amount, 0.01);
    }

    public function test_a_callback_validated_for_less_than_the_intent_is_refused(): void
    {
        $this->enableSslcommerz();
        $this->fakeInitSuccess();

        $customer = $this->customer();
        $booking  = $this->booking($customer, 25000);

        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/pay", ['method' => 'sslcommerz'])
            ->assertOk();

        $intent = PaymentIntent::first();

        // The payer (or someone else) got the provider to validate 1 taka.
        $this->fakeValidation($intent->reference, 1);

        $this->providerCallback($intent->reference)->assertStatus(402);

        $this->assertSame(PaymentIntent::STATUS_FAILED, $intent->fresh()->status);
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertEqualsWithDelta(0, (float) Invoice::where('booking_id', $booking->id)->sum('paid_amount'), 0.01);
    }

    public function test_a_callback_that_the_provider_does_not_validate_settles_nothing(): void
    {
        $this->enableSslcommerz();
        $this->fakeInitSuccess();

        $customer = $this->customer();
        $booking  = $this->booking($customer);

        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/pay", ['method' => 'sslcommerz'])
            ->assertOk();

        $intent = PaymentIntent::first();

        Http::fake([
            'sandbox.sslcommerz.com/validator/*' => Http::response([
                'status' => 'INVALID_TRANSACTION',
            ], 200),
        ]);

        // The callback claims success; only the validation API is believed.
        $this->providerCallback($intent->reference, ['status' => 'VALID', 'val_id' => 'FORGED'])->assertStatus(402);

        $this->assertSame(PaymentIntent::STATUS_FAILED, $intent->fresh()->status);
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_with_no_gateway_enabled_the_record_payment_flow_is_unchanged(): void
    {
        // No credentials, no status — a fresh install.
        $customer = $this->customer();
        $booking  = $this->booking($customer, 12000);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/pay", ['method' => 'bKash']);

        $response->assertOk();
        $this->assertNull($response->json('data.redirect_url'));
        $this->assertTrue($response->json('data.awaiting_confirmation'));

        // No gateway means the customer is declaring a payment, not making
        // one — the desk confirms it, same as the web portal. Marking the
        // booking paid on the customer's word alone would issue a receipt
        // and create the selling agent's commission before the money is
        // actually in.
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame(0, PaymentIntent::count());

        $invoice = Invoice::where('booking_id', $booking->id)->firstOrFail();
        $this->assertEqualsWithDelta(0, (float) $invoice->paid_amount, 0.01);
    }

    public function test_an_unknown_reference_is_rejected(): void
    {
        $this->enableSslcommerz();

        $this->providerCallback('TRV-000000-NOPE')->assertStatus(402);
    }
}
