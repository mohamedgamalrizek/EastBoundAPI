<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Refund;
use App\Services\Accounting\BillingService;
use Illuminate\Support\Carbon;

/**
 * The admin-configurable rule for a *customer-initiated* cancellation:
 * a minimum lead time before the booking's travel date, and a percentage
 * penalty the agency keeps out of whatever was already paid.
 *
 * Settings (Settings > General, "Booking Policy"):
 *   - booking_cancellation_window_hours   (int, default 24)
 *   - booking_cancellation_penalty_percent (int 0-100, default 10)
 *
 * This only governs self-service cancellation (the customer app / portal).
 * Back-office staff cancelling a booking from Bookings > Manage are not
 * subject to it — that stays a manual, case-by-case call.
 */
class CancellationPolicy
{
    /** Hours of lead time the customer must give before their travel date. */
    public static function windowHours(): int
    {
        $hours = settings('booking_cancellation_window_hours');

        return $hours === null || $hours === '' ? 24 : max(0, (int) $hours);
    }

    /** Percentage of the paid amount the agency retains on cancellation. */
    public static function penaltyPercent(): int
    {
        $percent = settings('booking_cancellation_penalty_percent');
        $percent = $percent === null || $percent === '' ? 10 : (int) $percent;

        return max(0, min(100, $percent));
    }

    /**
     * Whether the booking's travel date is still far enough away for a
     * customer to cancel it themselves. A booking with no travel date (should
     * not happen, but Booking::travel_date is nullable at the type level) is
     * never blocked by the window.
     */
    public static function isWithinCancellableWindow(Booking $booking): bool
    {
        if (! $booking->travel_date) {
            return true;
        }

        $travelStart = Carbon::parse($booking->travel_date)->startOfDay();

        // Hours between now and the travel date (negative once it has passed).
        $hoursUntilTravel = now()->diffInHours($travelStart, false);

        return $hoursUntilTravel >= self::windowHours();
    }

    /** Whether the booking's travel date has already gone by. */
    public static function hasTravelDatePassed(Booking $booking): bool
    {
        return $booking->travel_date
            && Carbon::parse($booking->travel_date)->startOfDay()->isPast();
    }

    /**
     * Split a paid amount into what the agency keeps (penalty) and what goes
     * back to the customer, per the configured penalty percentage. Both
     * figures are rounded to 2dp so they always add back up to $paidAmount.
     */
    public static function split(float $paidAmount): array
    {
        $penaltyPercent = self::penaltyPercent();
        $penaltyAmount  = round($paidAmount * $penaltyPercent / 100, 2);
        $refundAmount   = round($paidAmount - $penaltyAmount, 2);

        return [
            'penalty_percent' => $penaltyPercent,
            'penalty_amount'  => $penaltyAmount,
            'refund_amount'   => $refundAmount,
        ];
    }

    /**
     * Refund a paid booking net of the cancellation penalty. Mirrors
     * BillingService::refundBooking() (same invoice lookup, same "nothing to
     * refund" guard) but posts the penalty-adjusted amount instead of the
     * full paid amount, via BillingService::refund() which takes an explicit
     * amount.
     *
     * Returns null when there is no invoice or nothing left to refund;
     * otherwise the Refund plus the breakdown that produced its amount.
     */
    public static function refundBooking(Booking $booking, string $method, ?string $reason = null): ?array
    {
        $billing = app(BillingService::class);

        $invoice = Invoice::where('booking_id', $booking->id)->first();
        if (! $invoice) {
            return null;
        }

        $paidAmount = $billing->refundableAmount($invoice);
        if ($paidAmount <= 0.009) {
            return null;
        }

        $breakdown = self::split($paidAmount);

        if ($breakdown['refund_amount'] <= 0.009) {
            return ['refund' => null] + $breakdown;
        }

        /** @var Refund $refund */
        $refund = $billing->refund($invoice, $breakdown['refund_amount'], $method, $reason);

        return ['refund' => $refund] + $breakdown;
    }
}
