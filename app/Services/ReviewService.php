<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Review;
use Illuminate\Support\Carbon;

/**
 * Who may review what, and what happens when they do.
 *
 * The rule that makes a review worth reading is that it can only come from a
 * booking the customer paid for and has now travelled on. That is checked
 * here, once, so the app, the portal and the website cannot drift apart on
 * what "verified" means.
 *
 * Moderation is on by default: a new review is `pending` and invisible until
 * the office approves it. An agency that would rather publish straight away
 * can switch `review_auto_approve` on in Settings > Integrations > Reviews.
 */
class ReviewService
{
    /** Publish reviews without the office looking at them first? */
    public function autoApproves(): bool
    {
        return (string) settings('review_auto_approve') === '1';
    }

    /**
     * Why this booking cannot be reviewed, or null when it can.
     *
     * Kept message-shaped rather than boolean because "not yet" and "already
     * done" are different things to tell a customer, and every surface wants
     * to tell them the same way.
     */
    public function rejectionReason(Booking $booking, Customer $customer): ?string
    {
        if ((int) $booking->customer_id !== (int) $customer->id) {
            return 'This booking is not yours to review.';
        }
        if (! $booking->package_id) {
            return 'This booking has no tour attached to review.';
        }
        if (strtolower((string) $booking->status) === 'cancelled') {
            return 'A cancelled booking cannot be reviewed.';
        }
        if (strtolower((string) $booking->status) !== 'paid') {
            return 'You can review a tour once the booking is paid.';
        }
        if (! $this->hasTravelled($booking)) {
            return 'You can review this tour after your travel date.';
        }
        if ($this->existingFor($booking)) {
            return 'You have already reviewed this trip.';
        }

        return null;
    }

    public function canReview(Booking $booking, Customer $customer): bool
    {
        return $this->rejectionReason($booking, $customer) === null;
    }

    /** The review already written against this booking, if any. */
    public function existingFor(Booking $booking): ?Review
    {
        return Review::where('booking_id', $booking->id)->first();
    }

    /**
     * Bookings of this customer that are waiting to be reviewed — what the
     * portal and the app prompt on ("How was your trip?").
     */
    public function awaitingReview(Customer $customer)
    {
        return Booking::with('package')
            ->where('customer_id', $customer->id)
            ->where('status', 'paid')
            ->whereNotNull('package_id')
            ->whereDate('travel_date', '<=', today())
            ->whereDoesntHave('review')
            ->latest('travel_date')
            ->get();
    }

    /**
     * Record a review. Callers must have checked rejectionReason() first;
     * the unique index on (booking_id, customer_id) is the backstop against a
     * double submit slipping past that check.
     */
    public function create(Booking $booking, Customer $customer, int $rating, ?string $title, ?string $comment): Review
    {
        return Review::create([
            'customer_id' => $customer->id,
            'package_id'  => $booking->package_id,
            'booking_id'  => $booking->id,
            'rating'      => max(1, min(5, $rating)),
            'title'       => $title,
            'comment'     => $comment,
            'status'      => $this->autoApproves() ? Review::APPROVED : Review::PENDING,
        ]);
    }

    private function hasTravelled(Booking $booking): bool
    {
        // No travel date should not block a review of a trip that is paid for
        // and closed — an imported or desk-keyed booking can be missing one.
        return ! $booking->travel_date
            || Carbon::parse($booking->travel_date)->startOfDay()->lte(today());
    }
}
