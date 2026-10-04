<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Models\Package;
use App\Models\Booking;
use App\Models\TourGuide;
use App\Models\TourGuideAssignment;
use App\Models\TourGuideRating;
use App\Models\Review;
use App\Models\TourSchedule;
use App\Services\Booking\CancellationPolicy;
use App\Services\ReviewService;

/**
 * Shared shaping helpers for the mobile API so Home / Tour / Booking
 * controllers return tours in one consistent format.
 */
trait ApiTransformTrait
{
    /**
     * Resolve a stored image string into a full URL.
     * Mirrors the web frontend logic (http passthrough, asset(), bundled placeholder).
     */
    protected function imageUrl(?string $image, string $seed = 'pkg'): string
    {
        // Already a full URL.
        if ($image && Str::startsWith($image, 'http')) {
            return $image;
        }

        // Looks like a real stored file path (has a slash or extension) → asset URL.
        if ($image && (Str::contains($image, '/') || Str::contains($image, '.'))) {
            return asset($image);
        }

        // Otherwise (empty, or a plain label like "slider1") → bundled placeholder.
        return placeholder_image('card');
    }

    /**
     * Compact tour representation used in lists / home / cards.
     */
    protected function tourCard(Package $package): array
    {
        return [
            'id'              => $package->id,
            'title'           => $package->title,
            'destination'     => $package->destination,
            'category'        => $package->category,
            // Bookings bill price × travellers; the unit says so explicitly so
            // clients don't render it as a whole-trip total.
            'price'           => (float) $package->price,
            'price_unit'      => 'per_adult',
            'child_price'     => $package->child_price !== null ? (float) $package->child_price : null,
            'single_supplement' => $package->single_supplement !== null ? (float) $package->single_supplement : null,
            'duration_days'   => (int) $package->duration_days,
            'duration_nights' => (int) $package->duration_nights,
            'image_url'       => $this->imageUrl($package->image, 'pkg' . $package->id),
            // Null when nobody has reviewed it: "unrated" and "rated 0" are
            // different things and the app draws them differently.
            'avg_rating'      => $package->avg_rating,
            'reviews_count'   => $package->reviews_count,
        ];
    }

    /**
     * Full tour detail (card + description + category + upcoming schedules).
     */
    protected function tourDetail(Package $package): array
    {
        $package->loadMissing(['packageCategory', 'tourSchedules', 'itineraries']);

        return array_merge($this->tourCard($package), [
            'description'      => $package->description,
            'status'           => $package->status,
            // Same per-package content the website's package page renders.
            'inclusions'       => $package->inclusionList(),
            'exclusions'       => $package->exclusionList(),
            'itinerary'        => $package->itineraries->map(fn ($day) => [
                'day_number'  => (int) $day->day_number,
                'title'       => $day->title,
                'description' => $day->description,
            ])->values(),
            'package_category' => $package->packageCategory ? [
                'id'   => $package->packageCategory->id,
                'name' => $package->packageCategory->name,
                'slug' => $package->packageCategory->slug,
            ] : null,
            // Schedules are open|full|closed — the old 'active' filter matched
            // nothing, so the app never saw any departures.
            'schedules'   => $package->tourSchedules
                ->where('status', '!=', 'closed')
                ->map(fn (TourSchedule $s) => $this->scheduleInfo($s))
                ->values(),
            // The guide leading the next departure, when one is assigned.
            'guide'       => ($next = TourGuideAssignment::with('guide.ratings')
                ->where('package_id', $package->id)
                ->active()
                ->where('end_date', '>=', today())
                ->orderBy('start_date')
                ->first()) ? $this->guideInfo($next->guide) : null,
            // The most recent approved reviews. The full list has its own
            // paginated endpoint; this is what the detail screen shows.
            'reviews'     => $package->reviews()->approved()->with('customer')->latest()->take(5)
                ->get()->map(fn (Review $r) => $this->reviewInfo($r))->values(),
        ]);
    }

    /** Public shape of a review. */
    protected function reviewInfo(Review $review): array
    {
        return [
            'id'         => $review->id,
            'rating'     => (int) $review->rating,
            'title'      => $review->title,
            'comment'    => $review->comment,
            'author'     => $review->authorName(),
            'verified'   => $review->isVerified(),
            'reply'      => $review->reply,
            'status'     => $review->status,
            'package_id' => $review->package_id,
            'created_at' => optional($review->created_at)->toDateTimeString(),
        ];
    }

    /** Public shape of a tour guide (booking detail, tour detail). */
    protected function guideInfo(?TourGuide $guide): ?array
    {
        if (! $guide) {
            return null;
        }

        return [
            'id'               => $guide->id,
            'name'             => $guide->name,
            'phone'            => $guide->phone,
            'photo_url'        => $guide->photo ? $this->imageUrl($guide->photo, 'guide' . $guide->id) : null,
            'languages'        => $guide->languages,
            'experience_years' => (int) $guide->experience_years,
            'avg_rating'       => $guide->avg_rating,
        ];
    }

    /**
     * The guide assignment covering this booking's departure: same package,
     * travel date inside the assignment window, not cancelled.
     */
    protected function bookingGuideAssignment(Booking $booking): ?TourGuideAssignment
    {
        if (! $booking->package_id || ! $booking->travel_date) {
            return null;
        }

        return TourGuideAssignment::with('guide.ratings')
            ->where('package_id', $booking->package_id)
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '<=', $booking->travel_date)
            ->where('end_date', '>=', $booking->travel_date)
            ->orderBy('start_date')
            ->first();
    }

    /**
     * Booking representation for the app (My Bookings list + detail).
     */
    protected function bookingInfo(Booking $booking): array
    {
        $booking->loadMissing('package');

        $assignment = $this->bookingGuideAssignment($booking);
        $rated = $assignment && TourGuideRating::where('tour_guide_assignment_id', $assignment->id)
            ->where('customer_id', $booking->customer_id)
            ->exists();

        // Whether this trip can be reviewed is the same rule the portal and
        // the website use, so the app cannot offer a button the server will
        // refuse. A booking with no customer (an office-keyed walk-in) is
        // never reviewable from the app.
        $booking->loadMissing('review');
        $reviews       = app(ReviewService::class);
        $reviewReason  = $booking->customer
            ? $reviews->rejectionReason($booking, $booking->customer)
            : 'This booking is not linked to an account.';

        return [
            'id'            => $booking->id,
            'reference'     => 'BKG-' . str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT),
            'customer_name' => $booking->customer_name,
            'travel_date'   => optional($booking->travel_date)->toDateString(),
            'travelers'     => (int) $booking->travelers,
            'amount'        => (float) $booking->amount,
            // What it would have cost, and what came off it. `amount` is
            // still the payable figure, so clients that ignore these are
            // unaffected.
            'gross_amount'    => $booking->grossAmount(),
            'coupon_code'     => $booking->coupon_code,
            'coupon_discount' => (float) $booking->coupon_discount,
            'points_redeemed' => (int) $booking->points_redeemed,
            'points_discount' => (float) $booking->points_discount,
            'total_discount'  => $booking->totalDiscount(),
            'status'        => $booking->status,
            'created_at'    => optional($booking->created_at)->toDateTimeString(),
            'package'       => $booking->package ? [
                'id'          => $booking->package->id,
                'title'       => $booking->package->title,
                'destination' => $booking->package->destination,
                'image_url'   => $this->imageUrl($booking->package->image, 'pkg' . $booking->package->id),
            ] : null,
            'guide'               => $assignment ? $this->guideInfo($assignment->guide) : null,
            'guide_assignment_id' => $assignment?->id,
            'guide_rated'         => $rated,
            // Rate once the tour has happened (assignment closed or window past).
            'guide_can_rate'      => $assignment && ! $rated && $booking->status !== 'cancelled'
                && ($assignment->status === 'completed' || $assignment->end_date->isPast()),
            // Lets the app show/hide the "Cancel booking" action and explain
            // the penalty up front rather than the customer finding out only
            // after tapping cancel.
            'can_cancel'                     => $booking->status !== 'cancelled'
                && CancellationPolicy::isWithinCancellableWindow($booking),
            'cancellation_window_hours'      => CancellationPolicy::windowHours(),
            'cancellation_penalty_percent'   => CancellationPolicy::penaltyPercent(),
            // Review state: what was written, whether another may be, and the
            // reason when it may not — so the app can explain rather than
            // simply hiding the button.
            'review'            => $booking->review ? $this->reviewInfo($booking->review) : null,
            'can_review'        => $reviewReason === null,
            'review_blocked_by' => $reviewReason,
        ];
    }

    protected function scheduleInfo(TourSchedule $schedule): array
    {
        $seats     = (int) $schedule->seats;
        $booked    = (int) $schedule->booked;

        return [
            'id'         => $schedule->id,
            'start_date' => optional($schedule->start_date)->toDateString(),
            'end_date'   => optional($schedule->end_date)->toDateString(),
            'seats'      => $seats,
            'booked'     => $booked,
            'available'  => max(0, $seats - $booked),
            'status'     => $schedule->status,
        ];
    }
}
