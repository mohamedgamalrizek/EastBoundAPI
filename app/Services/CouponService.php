<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

/**
 * Looking a promo code up, and keeping `used_count` honest once one has been
 * spent. The rules themselves live on the model (Coupon::rejectionReason);
 * this is the part that touches the database.
 *
 * Every booking path goes through App\Services\Booking\BookingPricing, which
 * calls this — so a code behaves identically whether it was typed into the
 * app, the customer portal, the public website or the back-office form.
 */
class CouponService
{
    /** Find an active coupon by code, case-insensitively. Null when unknown. */
    public function find(?string $code): ?Coupon
    {
        $code = trim((string) $code);

        if ($code === '') {
            return null;
        }

        return Coupon::whereRaw('LOWER(code) = ?', [mb_strtolower($code)])->first();
    }

    /**
     * Claim one use of a coupon for a booking.
     *
     * The increment runs as an atomic conditional UPDATE rather than a read,
     * add and save: two customers redeeming the last remaining use at the same
     * moment would both pass a read-then-write check and the limit would be
     * overspent. When the UPDATE matches no row the limit is genuinely gone
     * and the caller is told so.
     */
    public function claim(Coupon $coupon): bool
    {
        $claimed = Coupon::whereKey($coupon->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->update(['used_count' => DB::raw('used_count + 1')]);

        if ($claimed) {
            $coupon->used_count = $coupon->used_count + 1;
        }

        return (bool) $claimed;
    }

    /**
     * Hand a use back — a cancelled booking should not burn the customer's
     * coupon. Floored at zero so a double release cannot drive the counter
     * negative and silently grant extra uses.
     */
    public function release(Booking $booking): void
    {
        if (! $booking->coupon_id) {
            return;
        }

        Coupon::whereKey($booking->coupon_id)
            ->where('used_count', '>', 0)
            ->update(['used_count' => DB::raw('used_count - 1')]);
    }
}
