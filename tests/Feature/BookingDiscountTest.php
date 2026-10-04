<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LoyaltyTransaction;
use App\Models\Package;
use App\Services\Booking\BookingPricing;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Promo codes and loyalty redemption.
 *
 * Both were half-built: the coupon module was CRUD no booking path ever read,
 * and points could be earned but never spent. These tests pin the behaviour
 * that makes them real — and, most importantly, that a discount reaches the
 * ledger as a smaller sale rather than as money appearing from nowhere.
 */
class BookingDiscountTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function pricing(): BookingPricing
    {
        return app(BookingPricing::class);
    }

    private function package(): Package
    {
        return Package::where('status', 'active')->firstOrFail();
    }

    private function customer(): Customer
    {
        return Customer::firstOrFail();
    }

    /** Give a customer a known balance without going through a booking. */
    private function grantPoints(Customer $customer, int $points): void
    {
        app(LoyaltyService::class)->adjust($customer, $points, 'Test balance');
    }

    private function book(array $overrides, array $quote): Booking
    {
        $booking = Booking::create(array_merge([
            'customer_id'   => $this->customer()->id,
            'package_id'    => $this->package()->id,
            'customer_name' => $this->customer()->name,
            'travel_date'   => now()->addDays(20)->toDateString(),
            'travelers'     => 1,
            'status'        => 'pending',
        ], $overrides) + $this->pricing()->columns($quote));

        $this->pricing()->commit($booking, $quote);

        return $booking->fresh();
    }

    public function test_percentage_coupon_comes_off_the_price(): void
    {
        $coupon = Coupon::create([
            'code' => 'TEST10', 'type' => Coupon::PERCENTAGE, 'value' => 10,
            'min_spend' => 0, 'status' => 'active',
        ]);

        $quote = $this->pricing()->quote(50000, null, 'test10'); // codes are case-insensitive

        $this->assertSame(5000.0, $quote['coupon_discount']);
        $this->assertSame(45000.0, $quote['amount']);
        $this->assertSame($coupon->id, $quote['coupon_id']);
        $this->assertNull($quote['coupon_error']);
    }

    public function test_a_fixed_coupon_can_never_exceed_the_booking(): void
    {
        Coupon::create(['code' => 'BIG', 'type' => Coupon::FIXED, 'value' => 99999, 'status' => 'active']);

        $quote = $this->pricing()->quote(1000, null, 'BIG');

        // A negative sale would post a negative invoice to the ledger.
        $this->assertSame(1000.0, $quote['coupon_discount']);
        $this->assertSame(0.0, $quote['amount']);
    }

    public function test_unusable_coupons_are_explained_and_never_block_the_booking(): void
    {
        Coupon::create(['code' => 'SPENT', 'type' => Coupon::FIXED, 'value' => 100, 'usage_limit' => 1, 'used_count' => 1, 'status' => 'active']);
        Coupon::create(['code' => 'GONE', 'type' => Coupon::FIXED, 'value' => 100, 'expires_at' => now()->subDay(), 'status' => 'active']);
        Coupon::create(['code' => 'OFF', 'type' => Coupon::FIXED, 'value' => 100, 'status' => 'inactive']);
        Coupon::create(['code' => 'RICH', 'type' => Coupon::FIXED, 'value' => 100, 'min_spend' => 99999, 'status' => 'active']);

        foreach (['SPENT', 'GONE', 'OFF', 'RICH', 'NOSUCHCODE'] as $code) {
            $quote = $this->pricing()->quote(5000, null, $code);

            $this->assertNotNull($quote['coupon_error'], "{$code} should be refused");
            $this->assertSame(0.0, $quote['coupon_discount'], "{$code} must not discount");
            $this->assertSame(5000.0, $quote['amount'], "{$code} must leave the price alone");
        }
    }

    public function test_usage_limit_is_counted_and_returned_on_cancellation(): void
    {
        $coupon = Coupon::create(['code' => 'ONCE', 'type' => Coupon::FIXED, 'value' => 500, 'usage_limit' => 1, 'status' => 'active']);

        $booking = $this->book([], $this->pricing()->quote(5000, null, 'ONCE'));

        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertSame('4500.00', $booking->amount);

        // A second booking cannot have it while the first holds the only use.
        $this->assertNotNull($this->pricing()->quote(5000, null, 'ONCE')['coupon_error']);

        $booking->update(['status' => 'cancelled']);
        $this->assertSame(0, $coupon->fresh()->used_count, 'a cancelled booking must hand the use back');

        // And taking it again when the booking is reinstated.
        $booking->update(['status' => 'confirmed']);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_points_are_spent_and_returned_with_the_booking(): void
    {
        $customer = $this->customer();
        $this->grantPoints($customer, 5000);
        $loyalty = app(LoyaltyService::class);

        $quote   = $this->pricing()->quote(50000, $customer, null, 2000);
        $booking = $this->book([], $quote);

        $this->assertSame(2000.0, $quote['points_discount']);
        $this->assertSame(3000, $loyalty->balance($customer), 'points should leave the balance');
        $this->assertSame(2000, $booking->points_redeemed);

        $booking->update(['status' => 'cancelled']);
        $this->assertSame(5000, $loyalty->balance($customer), 'a cancelled booking returns the points');
    }

    public function test_points_respect_the_configured_limits(): void
    {
        $customer = $this->customer();
        $this->grantPoints($customer, 10000);

        // Below the minimum.
        $this->assertStringContainsString('at least', (string) $this->pricing()->quote(50000, $customer, null, 10)['points_error']);

        // More than they hold.
        $this->assertStringContainsString('only have', (string) $this->pricing()->quote(50000, $customer, null, 999999)['points_error']);

        // More than the share of a booking points may cover (50% of 10000).
        $this->assertStringContainsString('at most', (string) $this->pricing()->quote(10000, $customer, null, 9000)['points_error']);
    }

    public function test_the_coupon_is_applied_before_points(): void
    {
        $customer = $this->customer();
        $this->grantPoints($customer, 5000);
        Coupon::create(['code' => 'HALF', 'type' => Coupon::PERCENTAGE, 'value' => 50, 'status' => 'active']);

        $quote = $this->pricing()->quote(10000, $customer, 'HALF', 2500);

        // Coupon first: 50% of the list price, then points off the remainder.
        // The other order would discount the points themselves.
        $this->assertSame(5000.0, $quote['coupon_discount']);
        $this->assertSame(2500.0, $quote['points_discount']);
        $this->assertSame(2500.0, $quote['amount']);

        // And the points ceiling follows the remainder, not the list price:
        // half of what is left after the coupon, so 2,500 here.
        $this->assertStringContainsString(
            'at most',
            (string) $this->pricing()->quote(10000, $customer, 'HALF', 5000)['points_error']
        );
    }

    public function test_the_invoice_bills_the_discounted_amount(): void
    {
        Coupon::create(['code' => 'TENOFF', 'type' => Coupon::PERCENTAGE, 'value' => 10, 'status' => 'active']);

        $booking = $this->book([], $this->pricing()->quote(10000, null, 'TENOFF'));
        $booking->update(['status' => 'paid', 'payment_method' => 'Cash']);

        $invoice = Invoice::where('booking_id', $booking->id)->firstOrFail();

        // The books must see the sale that was actually made — not the list
        // price, which would overstate revenue by the discount.
        $this->assertSame('9000.00', $invoice->amount);
        $this->assertSame('10000.00', $booking->fresh()->gross_amount);
    }

    public function test_points_are_earned_on_what_was_paid_and_reversed_on_cancellation(): void
    {
        $customer = $this->customer();
        $loyalty  = app(LoyaltyService::class);
        Coupon::create(['code' => 'HALFOFF', 'type' => Coupon::PERCENTAGE, 'value' => 50, 'status' => 'active']);

        $booking = $this->book([], $this->pricing()->quote(100000, null, 'HALFOFF'));
        $booking->update(['status' => 'paid', 'payment_method' => 'Cash']);

        // 50,000 paid at the default 0.01 rate.
        $this->assertSame(500, $loyalty->balance($customer));

        $booking->update(['status' => 'cancelled']);
        $this->assertSame(0, $loyalty->balance($customer), 'a cancelled trip earns nothing');
        $this->assertDatabaseHas('loyalty_transactions', ['reference' => 'reversal:booking:' . $booking->id]);
    }

    public function test_a_booking_written_without_a_quote_still_records_its_list_price(): void
    {
        // The back-office form, an agent's sale and the seeders all write
        // `amount` directly; gross must not be left at zero.
        $booking = Booking::create([
            'customer_id' => $this->customer()->id, 'package_id' => $this->package()->id,
            'customer_name' => 'Walk-in', 'travel_date' => now()->addDays(5)->toDateString(),
            'travelers' => 1, 'amount' => 7500, 'status' => 'pending',
        ]);

        $this->assertSame('7500.00', $booking->fresh()->gross_amount);
        $this->assertFalse($booking->hasDiscount());
    }

    public function test_spending_points_twice_on_one_booking_is_a_no_op(): void
    {
        $customer = $this->customer();
        $this->grantPoints($customer, 5000);
        $loyalty = app(LoyaltyService::class);

        $booking = $this->book([], $this->pricing()->quote(50000, $customer, null, 1000));

        // A re-save (a back-office edit, a retried request) must not charge
        // the customer's points a second time.
        $this->assertTrue($loyalty->spendOnBooking($booking, 1000));
        $this->assertSame(4000, $loyalty->balance($customer));
        $this->assertSame(1, LoyaltyTransaction::where('reference', 'redeem:booking:' . $booking->id)->count());
    }
}
