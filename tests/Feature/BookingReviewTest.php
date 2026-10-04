<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Review;
use App\Models\Backend\Setting;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Verified booking reviews.
 *
 * The claim the public page makes is that a review came from somebody who
 * paid for the trip and took it. These tests are what makes that claim true:
 * every other route into a review has to be refused.
 */
class BookingReviewTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function reviews(): ReviewService
    {
        return app(ReviewService::class);
    }

    private function customer(): Customer
    {
        return Customer::firstOrFail();
    }

    private function package(): Package
    {
        return Package::where('status', 'active')->firstOrFail();
    }

    private function booking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'customer_id'   => $this->customer()->id,
            'package_id'    => $this->package()->id,
            'customer_name' => $this->customer()->name,
            'travel_date'   => now()->subDays(3)->toDateString(),
            'travelers'     => 1,
            'amount'        => 5000,
            'status'        => 'paid',
        ], $overrides));
    }

    private function setSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(tenant_cache_prefix() . 'settings');
    }

    public function test_a_paid_and_travelled_booking_may_be_reviewed(): void
    {
        $this->assertNull($this->reviews()->rejectionReason($this->booking(), $this->customer()));
    }

    public function test_every_other_kind_of_booking_is_refused(): void
    {
        $cases = [
            'not yet travelled' => ['travel_date' => now()->addDays(10)->toDateString()],
            'not paid'          => ['status' => 'pending'],
            'cancelled'         => ['status' => 'cancelled'],
            'no tour attached'  => ['package_id' => null],
        ];

        foreach ($cases as $label => $overrides) {
            $reason = $this->reviews()->rejectionReason($this->booking($overrides), $this->customer());
            $this->assertNotNull($reason, "{$label} should be refused");
        }
    }

    public function test_a_customer_cannot_review_somebody_elses_trip(): void
    {
        $other = Customer::where('id', '!=', $this->customer()->id)->firstOrFail();

        $this->assertSame(
            'This booking is not yours to review.',
            $this->reviews()->rejectionReason($this->booking(), $other)
        );
    }

    public function test_one_review_per_trip(): void
    {
        $booking = $this->booking();
        $this->reviews()->create($booking, $this->customer(), 5, 'Great', 'Loved it.');

        $this->assertStringContainsString(
            'already reviewed',
            (string) $this->reviews()->rejectionReason($booking->fresh(), $this->customer())
        );
    }

    public function test_reviews_wait_for_moderation_by_default(): void
    {
        $this->setSetting('review_auto_approve', '0');

        $review = $this->reviews()->create($this->booking(), $this->customer(), 4, null, 'Fine.');

        $this->assertSame(Review::PENDING, $review->status);
        $this->assertTrue($review->isVerified());
    }

    public function test_auto_approve_publishes_immediately(): void
    {
        $this->setSetting('review_auto_approve', '1');

        $this->assertSame(
            Review::APPROVED,
            $this->reviews()->create($this->booking(), $this->customer(), 4, null, 'Fine.')->status
        );
    }

    public function test_only_approved_reviews_count_towards_a_rating(): void
    {
        $this->setSetting('review_auto_approve', '0');
        $package = $this->package();

        $review = $this->reviews()->create($this->booking(), $this->customer(), 5, null, 'Great.');

        $this->assertNull(Package::find($package->id)->avg_rating, 'a pending review must not lift the rating');

        $review->update(['status' => Review::APPROVED]);

        $this->assertSame(5.0, Package::find($package->id)->avg_rating);
        $this->assertSame(1, Package::find($package->id)->reviews_count);
    }

    public function test_the_public_package_page_shows_approved_reviews_only(): void
    {
        $this->setSetting('review_auto_approve', '0');
        $package = $this->package();

        $review = $this->reviews()->create($this->booking(), $this->customer(), 5, 'Unmissable', 'Worth every taka.');

        $this->get(route('front.package', $package->id))->assertOk()->assertDontSee('Unmissable');

        $review->update(['status' => Review::APPROVED]);

        $this->get(route('front.package', $package->id))->assertOk()
            ->assertSee('Unmissable')
            ->assertSee('Verified booking');
    }

    public function test_staff_can_moderate_but_not_rewrite_a_review(): void
    {
        $admin  = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
        $review = $this->reviews()->create($this->booking(), $this->customer(), 2, 'Meh', 'Bus was late.');

        $this->actingAs($admin)
            ->put(route('review.update'), ['id' => $review->id, 'status' => Review::APPROVED, 'reply' => 'Sorry — we have changed operator.'])
            ->assertRedirect(route('review.index'));

        $review->refresh();
        $this->assertSame(Review::APPROVED, $review->status);
        $this->assertSame('Sorry — we have changed operator.', $review->reply);
        $this->assertNotNull($review->replied_at);

        // The customer's own words are untouched by moderation.
        $this->assertSame(2, $review->rating);
        $this->assertSame('Meh', $review->title);
        $this->assertSame('Bus was late.', $review->comment);
    }

    public function test_one_click_approve_and_reject(): void
    {
        $admin  = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
        $review = $this->reviews()->create($this->booking(), $this->customer(), 5, null, 'Great.');

        $this->actingAs($admin)->post(route('review.approve', $review->id))->assertRedirect(route('review.index'));
        $this->assertSame(Review::APPROVED, $review->fresh()->status);

        $this->actingAs($admin)->post(route('review.reject', $review->id))->assertRedirect(route('review.index'));
        $this->assertSame(Review::REJECTED, $review->fresh()->status);
    }

    public function test_the_api_refuses_a_review_of_a_trip_not_taken(): void
    {
        $customer = $this->customer();
        $booking  = $this->booking(['travel_date' => now()->addDays(10)->toDateString()]);
        $token    = $customer->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/bookings/' . $booking->id . '/review', ['rating' => 5])
            ->assertStatus(422);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_the_api_accepts_a_review_of_a_completed_trip(): void
    {
        $customer = $this->customer();
        $booking  = $this->booking();
        $token    = $customer->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/bookings/' . $booking->id . '/review', [
                'rating' => 5, 'title' => 'Brilliant', 'comment' => 'Would go again.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.review.verified', true);

        $this->assertDatabaseHas('reviews', ['booking_id' => $booking->id, 'rating' => 5]);
    }
}
