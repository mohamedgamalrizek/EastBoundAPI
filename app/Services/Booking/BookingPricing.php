<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Customer;
use App\Services\CouponService;
use App\Services\LoyaltyService;

/**
 * What a booking costs once a promo code and loyalty points have been taken
 * off it — and the one place that decides it.
 *
 * A tour booking can be raised from five places (the mobile app, the customer
 * portal, the public website's Book Now, a website enquiry, and the
 * back-office form). Each of them used to work out `amount` for itself, which
 * is why the coupon module could exist for months without a single booking
 * ever honouring a code. Every one of them now quotes through here, so a code
 * and a points balance behave the same wherever they are entered.
 *
 * Order of operations: the coupon comes off the list price first, then points
 * come off what is left. Doing it the other way round would let a percentage
 * coupon discount the points as well, quietly paying the customer's reward
 * back to them at a fraction of its value.
 *
 * `amount` remains the net figure the customer owes, so BillingService, the
 * invoice, the receipt, agent commission and the ledger need no knowledge of
 * discounts at all — they simply bill the sale that was actually made.
 */
class BookingPricing
{
    public function __construct(
        protected CouponService $coupons,
        protected LoyaltyService $loyalty,
    ) {}

    /**
     * Price a booking. Writes nothing: a quote is also what the "apply code"
     * button and the checkout preview need.
     *
     * A code that cannot be used does not fail the booking — the booking is
     * priced without it and the reason comes back in `coupon_error`, so a
     * customer never loses a half-filled form over a typo'd coupon.
     *
     * @return array{gross_amount:float,coupon:?Coupon,coupon_id:?int,coupon_code:?string,
     *               coupon_discount:float,points_redeemed:int,points_discount:float,
     *               total_discount:float,amount:float,coupon_error:?string,points_error:?string}
     */
    public function quote(float $gross, ?Customer $customer = null, ?string $couponCode = null, int $points = 0): array
    {
        $gross = round(max(0, $gross), 2);

        $quote = [
            'gross_amount'    => $gross,
            'coupon'          => null,
            'coupon_id'       => null,
            'coupon_code'     => null,
            'coupon_discount' => 0.0,
            'points_redeemed' => 0,
            'points_discount' => 0.0,
            'total_discount'  => 0.0,
            'amount'          => $gross,
            'coupon_error'    => null,
            'points_error'    => null,
        ];

        $quote = $this->applyCoupon($quote, $couponCode);
        $quote = $this->applyPoints($quote, $customer, $points);

        $quote['total_discount'] = round($quote['coupon_discount'] + $quote['points_discount'], 2);
        $quote['amount']         = round(max(0, $gross - $quote['total_discount']), 2);

        return $quote;
    }

    private function applyCoupon(array $quote, ?string $code): array
    {
        if (trim((string) $code) === '') {
            return $quote;
        }

        $coupon = $this->coupons->find($code);

        if (! $coupon) {
            $quote['coupon_error'] = 'That coupon code was not recognised.';

            return $quote;
        }

        if ($reason = $coupon->rejectionReason($quote['gross_amount'])) {
            $quote['coupon_error'] = $reason;

            return $quote;
        }

        $quote['coupon']          = $coupon;
        $quote['coupon_id']       = $coupon->id;
        $quote['coupon_code']     = $coupon->code;
        $quote['coupon_discount'] = $coupon->discountOn($quote['gross_amount']);

        return $quote;
    }

    private function applyPoints(array $quote, ?Customer $customer, int $points): array
    {
        if ($points <= 0 || ! $customer) {
            return $quote;
        }

        // Points buy down what is left after the coupon, never the list price.
        $remaining = round($quote['gross_amount'] - $quote['coupon_discount'], 2);

        if ($error = $this->loyalty->redemptionError($customer, $points, $remaining)) {
            $quote['points_error'] = $error;

            return $quote;
        }

        $quote['points_redeemed'] = $points;
        $quote['points_discount'] = min($this->loyalty->valueOf($points), $remaining);

        return $quote;
    }

    /**
     * The columns a quote contributes to a Booking. Spread into the array
     * passed to Booking::create() alongside the caller's own fields.
     */
    public function columns(array $quote): array
    {
        return [
            'amount'          => $quote['amount'],
            'gross_amount'    => $quote['gross_amount'],
            'coupon_id'       => $quote['coupon_id'],
            'coupon_code'     => $quote['coupon_code'],
            'coupon_discount' => $quote['coupon_discount'],
            'points_redeemed' => $quote['points_redeemed'],
            'points_discount' => $quote['points_discount'],
        ];
    }

    /**
     * Secure the discounts a freshly created booking was quoted: claim the
     * coupon use and spend the points.
     *
     * Both can still fail here even though the quote allowed them, because
     * between quoting and committing somebody else may have taken the last
     * use of the coupon or the customer may have spent the points on another
     * booking. Rather than fail the booking — the customer has already
     * committed to the trip — the booking is re-priced to what was actually
     * secured, and the caller is told what changed so it can say so.
     *
     * @return array<string> messages describing any discount that fell away
     */
    public function commit(Booking $booking, array $quote): array
    {
        $lost = [];

        if ($quote['coupon'] instanceof Coupon && ! $this->coupons->claim($quote['coupon'])) {
            $lost[] = 'The coupon reached its usage limit before your booking was saved, so it was not applied.';
            $booking->forceFill([
                'coupon_id'       => null,
                'coupon_code'     => null,
                'coupon_discount' => 0,
            ]);
        }

        if ($quote['points_redeemed'] > 0 && ! $this->loyalty->spendOnBooking($booking, $quote['points_redeemed'])) {
            $lost[] = 'Your points balance changed before the booking was saved, so they were not redeemed.';
            $booking->forceFill([
                'points_redeemed' => 0,
                'points_discount' => 0,
            ]);
        }

        if ($lost) {
            $booking->amount = round(max(0,
                (float) $booking->gross_amount
                - (float) $booking->coupon_discount
                - (float) $booking->points_discount
            ), 2);

            $booking->save();
        }

        return $lost;
    }

    /**
     * Hand the discounts back when a booking is cancelled: the coupon use is
     * released and the points return to the customer's balance.
     */
    public function release(Booking $booking): void
    {
        $this->coupons->release($booking);
        $this->loyalty->refundSpentPoints($booking);
    }

    /**
     * Re-price an existing booking after the desk has changed its amount or
     * its coupon code.
     *
     * Points already redeemed stay redeemed. The customer spent them against
     * this trip; an office edit to the price is not a reason to hand them
     * back, and doing so would silently raise what the customer owes. Only
     * the coupon is re-evaluated — pair this with releaseCoupon() so the
     * previous code's use is returned before the new one is claimed.
     */
    public function requote(Booking $booking, float $gross, ?string $couponCode): array
    {
        $gross = round(max(0, $gross), 2);
        $quote = $this->quote($gross, null, $couponCode, 0);

        // The carried-over points can never discount more than what is left
        // after the coupon, however far the desk drops the price.
        $pointsDiscount = min(
            (float) $booking->points_discount,
            max(0, $gross - $quote['coupon_discount'])
        );

        $quote['points_redeemed'] = (int) $booking->points_redeemed;
        $quote['points_discount'] = round($pointsDiscount, 2);
        $quote['total_discount']  = round($quote['coupon_discount'] + $quote['points_discount'], 2);
        $quote['amount']          = round(max(0, $gross - $quote['total_discount']), 2);

        return $quote;
    }

    /** Hand back only the coupon use, leaving any redeemed points alone. */
    public function releaseCoupon(Booking $booking): void
    {
        $this->coupons->release($booking);
    }

    /**
     * Take the discounts back when a cancelled booking is reinstated — the
     * mirror of release(), so a booking flipped back and forth ends up where
     * it started rather than accumulating free coupon uses and points.
     */
    public function reclaim(Booking $booking): void
    {
        if ($booking->coupon) {
            $this->coupons->claim($booking->coupon);
        }

        $this->loyalty->respendPoints($booking);
    }

    /**
     * What the checkout screens need to show before anything is saved: the
     * quote plus the customer's redemption allowance.
     */
    public function preview(float $gross, ?Customer $customer, ?string $couponCode = null, int $points = 0): array
    {
        $quote = $this->quote($gross, $customer, $couponCode, $points);

        unset($quote['coupon']);

        return $quote + [
            'points_balance'     => $customer ? $this->loyalty->balance($customer) : 0,
            'points_max'         => $customer ? $this->loyalty->maxRedeemablePoints($customer, $quote['gross_amount'] - $quote['coupon_discount']) : 0,
            'points_min'         => $this->loyalty->minRedeem(),
            'points_rate'        => $this->loyalty->redeemRate(),
            'points_max_percent' => $this->loyalty->maxRedeemPercent(),
        ];
    }
}
