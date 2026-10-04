<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Loyalty points: earning them, and — the half that was missing — spending
 * them.
 *
 * Points are spent as a discount on a booking, never converted into wallet
 * credit. The wallet is a real liability account (2300) that the double-entry
 * ledger reconciles against actual money; crediting it for points would
 * invent cash the agency never took, which is exactly why the old
 * `POST /wallet/topup` endpoint was removed in 1.1.0. A discount instead
 * lowers the sale, so the invoice, the receipt and the books all agree.
 *
 * Every row is written through firstOrCreate() on a unique `reference`, so a
 * retried request, a re-saved booking or a re-run observer cannot award or
 * spend the same points twice.
 *
 * Settings (Settings > Integrations > Loyalty):
 *   - loyalty_points_per_currency  points earned per unit of booking value
 *   - loyalty_referral_bonus       points for a successful referral
 *   - loyalty_redeem_rate          what one point is worth when spent
 *   - loyalty_min_redeem           fewest points that may be spent at once
 *   - loyalty_max_redeem_percent   most of a booking points may cover
 */
class LoyaltyService
{
    /* ---- Configuration --------------------------------------------------- */

    /** Currency value of a single point when redeemed. */
    public function redeemRate(): float
    {
        $rate = settings('loyalty_redeem_rate');

        return $rate === null || $rate === '' ? 1.0 : max(0.0, (float) $rate);
    }

    /** Fewest points that may be spent in one go. */
    public function minRedeem(): int
    {
        $min = settings('loyalty_min_redeem');

        return $min === null || $min === '' ? 100 : max(0, (int) $min);
    }

    /** The most of a booking, as a percentage, that points may pay for. */
    public function maxRedeemPercent(): int
    {
        $percent = settings('loyalty_max_redeem_percent');
        $percent = $percent === null || $percent === '' ? 50 : (int) $percent;

        return max(0, min(100, $percent));
    }

    public function earnRate(): float
    {
        return (float) (settings('loyalty_points_per_currency') ?: 0.01);
    }

    /* ---- Balance --------------------------------------------------------- */

    public function balance(Customer $customer): int
    {
        return (int) $customer->loyaltyTransactions()->sum('points');
    }

    /** What a number of points is worth in money. */
    public function valueOf(int $points): float
    {
        return round(max(0, $points) * $this->redeemRate(), 2);
    }

    /** Points needed to cover an amount (rounded up — no part-points). */
    public function pointsNeededFor(float $amount): int
    {
        $rate = $this->redeemRate();

        return $rate <= 0 ? 0 : (int) ceil($amount / $rate);
    }

    /**
     * The most points this customer could usefully spend on a subtotal: what
     * they hold, capped by the share of the booking points may cover, and by
     * what the remaining balance is actually worth.
     */
    public function maxRedeemablePoints(Customer $customer, float $subtotal): int
    {
        if ($this->redeemRate() <= 0 || $subtotal <= 0) {
            return 0;
        }

        $cashCap = round($subtotal * $this->maxRedeemPercent() / 100, 2);

        return max(0, min($this->balance($customer), $this->pointsNeededFor($cashCap)));
    }

    /**
     * Why these points cannot be spent on this subtotal, or null when they
     * can. Message-shaped because the app, the portal and the website all
     * want to say the same thing to the customer.
     */
    public function redemptionError(Customer $customer, int $points, float $subtotal): ?string
    {
        if ($points <= 0) {
            return null; // Redeeming nothing is not an error, it is the default.
        }
        if ($this->redeemRate() <= 0) {
            return 'Points cannot be redeemed at the moment.';
        }
        if ($points > $this->balance($customer)) {
            return 'You only have ' . number_format($this->balance($customer)) . ' points.';
        }
        if ($points < $this->minRedeem()) {
            return 'Redeem at least ' . number_format($this->minRedeem()) . ' points.';
        }
        if ($points > $this->maxRedeemablePoints($customer, $subtotal)) {
            return 'Points can cover at most ' . $this->maxRedeemPercent() . '% of this booking.';
        }

        return null;
    }

    /* ---- Earning --------------------------------------------------------- */

    public function ensureReferralCode(Customer $customer): string
    {
        if ($customer->referral_code) {
            return $customer->referral_code;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (Customer::where('referral_code', $code)->exists());

        $customer->forceFill(['referral_code' => $code])->saveQuietly();

        return $code;
    }

    /**
     * Award the points a paid booking earns — and take them back if that
     * booking later stops being paid.
     *
     * Points used to be awarded on payment and then simply left there when
     * the trip was cancelled and refunded, so a cancelled booking still paid
     * out rewards. The reversal is its own row rather than a deletion, for
     * the same reason a refund is its own record and not an edited receipt:
     * the customer's statement should show what happened.
     */
    public function awardPaidBooking(Booking $booking): void
    {
        if (! $booking->customer_id) {
            return;
        }

        $reference = 'booking:' . $booking->id;

        if (strtolower((string) $booking->status) !== 'paid') {
            $this->reverseEarned($booking, $reference);

            return;
        }

        // Points follow what the customer actually paid, so a discounted
        // booking earns on the discounted amount.
        $points = max(1, (int) floor(((float) $booking->amount) * $this->earnRate()));

        LoyaltyTransaction::firstOrCreate(['reference' => $reference], [
            'customer_id' => $booking->customer_id,
            'points'      => $points,
            'type'        => 'earn',
            'description' => 'Paid booking reward',
            'source_type' => Booking::class,
            'source_id'   => $booking->id,
        ]);
    }

    /** Counter-entry for points awarded on a booking that is no longer paid. */
    private function reverseEarned(Booking $booking, string $reference): void
    {
        $earned = LoyaltyTransaction::where('reference', $reference)->first();

        if (! $earned || $earned->points <= 0) {
            return;
        }

        LoyaltyTransaction::firstOrCreate(['reference' => 'reversal:' . $reference], [
            'customer_id' => $booking->customer_id,
            'points'      => -$earned->points,
            'type'        => 'reversal',
            'description' => 'Reward withdrawn — booking ' . $booking->status,
            'source_type' => Booking::class,
            'source_id'   => $booking->id,
        ]);
    }

    public function awardReferral(Customer $newCustomer): void
    {
        if (! $newCustomer->referred_by) {
            return;
        }

        LoyaltyTransaction::firstOrCreate(['reference' => 'referral:' . $newCustomer->id], [
            'customer_id' => $newCustomer->referred_by,
            'points'      => (int) (settings('loyalty_referral_bonus') ?: 100),
            'type'        => 'referral',
            'description' => 'Referral signup bonus',
            'source_type' => Customer::class,
            'source_id'   => $newCustomer->id,
        ]);
    }

    /* ---- Spending -------------------------------------------------------- */

    /**
     * Spend points against a booking.
     *
     * Balance is re-checked inside a locking transaction: the quote the
     * customer saw was computed before the booking was written, and two
     * bookings submitted at once would otherwise each pass a balance check
     * and together overspend the account. Returns false when the points are
     * no longer there, leaving the caller to price the booking without them.
     */
    public function spendOnBooking(Booking $booking, int $points): bool
    {
        if ($points <= 0 || ! $booking->customer_id) {
            return false;
        }

        $reference = 'redeem:booking:' . $booking->id;

        // Already spent against this booking. Re-checking the balance here
        // would compare the points being claimed against a total that has
        // ALREADY had them deducted, and report a shortfall that does not
        // exist — which is what a back-office edit to a discounted booking
        // would otherwise trigger.
        if (LoyaltyTransaction::where('reference', $reference)->exists()) {
            return true;
        }

        return (bool) DB::transaction(function () use ($booking, $points, $reference) {
            $held = (int) LoyaltyTransaction::where('customer_id', $booking->customer_id)
                ->lockForUpdate()
                ->sum('points');

            if ($held < $points) {
                return false;
            }

            LoyaltyTransaction::firstOrCreate(['reference' => $reference], [
                'customer_id' => $booking->customer_id,
                'points'      => -$points,
                'type'        => 'redeem',
                'description' => 'Redeemed against booking #' . $booking->id,
                'source_type' => Booking::class,
                'source_id'   => $booking->id,
            ]);

            return true;
        });
    }

    /**
     * Give back points spent on a booking that was cancelled. The customer
     * paid for the discount with points they no longer received value for.
     */
    public function refundSpentPoints(Booking $booking): void
    {
        if (! $booking->customer_id || (int) $booking->points_redeemed <= 0) {
            return;
        }

        $spent = LoyaltyTransaction::where('reference', 'redeem:booking:' . $booking->id)->first();

        if (! $spent) {
            return;
        }

        LoyaltyTransaction::firstOrCreate(['reference' => 'redeem-refund:booking:' . $booking->id], [
            'customer_id' => $booking->customer_id,
            'points'      => abs($spent->points),
            'type'        => 'refund',
            'description' => 'Points returned — booking #' . $booking->id . ' cancelled',
            'source_type' => Booking::class,
            'source_id'   => $booking->id,
        ]);
    }

    /**
     * Take the points back again when a cancelled booking is reinstated.
     *
     * This removes the refund row rather than posting a third entry against
     * it. Everywhere else a reversal is kept as its own record, because the
     * thing being reversed really happened — but a refund that is itself
     * undone describes nothing that ever stood, and stacking
     * refund-of-a-refund rows would make the customer's statement unreadable.
     * The original `redeem:` entry is untouched throughout.
     */
    public function respendPoints(Booking $booking): void
    {
        if ((int) $booking->points_redeemed <= 0) {
            return;
        }

        LoyaltyTransaction::where('reference', 'redeem-refund:booking:' . $booking->id)->delete();
    }

    /**
     * A manual correction from the back office (goodwill, a mistake, a
     * migrated balance). Unlike every other entry this has no source record,
     * so the reference carries a random suffix to stay unique.
     */
    public function adjust(Customer $customer, int $points, string $description): LoyaltyTransaction
    {
        return LoyaltyTransaction::create([
            'customer_id' => $customer->id,
            'points'      => $points,
            'type'        => 'adjustment',
            'description' => $description,
            'reference'   => 'adjust:' . $customer->id . ':' . Str::random(10),
        ]);
    }
}
