<?php

namespace App\Http\Controllers\Api;

use App\Models\Package;
use App\Actions\OpenVisaCaseForBooking;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\WalletTransaction;
use App\Services\Booking\BookingPricing;
use App\Services\Booking\CancellationPolicy;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Tour bookings for the Customer app. All routes are auth:sanctum and scoped
 * to the logged-in customer.
 */
class BookingController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait, ResolvesCustomer;

    /**
     * Create a booking for a tour package ("Book Now").
     */
    public function store(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can book.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'package_id'  => ['required', 'integer', 'exists:packages,id'],
            'travel_date' => ['required', 'date', 'after_or_equal:today'],
            'travelers'   => ['required', 'integer', 'min:1', 'max:50'],
            'notes'       => ['nullable', 'string', 'max:1000'],
            // The website's booking form collects who is travelling; the app
            // sends the same fields, falling back to the account on file.
            'customer_name'  => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'needs_visa'     => ['nullable', 'boolean'],
            // Discounts. Neither is required, and an unusable one never fails
            // the booking — see BookingPricing::quote().
            'coupon_code'     => ['nullable', 'string', 'max:60'],
            'points_redeemed' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $package = Package::where('status', 'active')->find($request->package_id);
        if (! $package) {
            return $this->responseWithError('Tour not available.', [], 404);
        }

        // A double tap, or a retry after a dropped response, must not open a
        // second booking for the same trip while the first is still pending —
        // each one would raise its own invoice. Same guard the website's
        // OpenTourBookingFromEnquiry applies.
        $existing = Booking::where('customer_id', $customer->id)
            ->where('package_id', $package->id)
            ->whereDate('travel_date', $request->travel_date)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            // The visa action is itself one-case-per-booking, so honouring the
            // flag here cannot duplicate a case — and a customer who ticked it
            // on the second attempt still gets one.
            $visaCase = $request->boolean('needs_visa')
                ? app(OpenVisaCaseForBooking::class)($existing, $package, $customer)
                : null;

            return $this->responseWithSuccess('Booking already open for this trip.', [
                'booking'          => $this->bookingInfo($existing),
                'visa_application' => $visaCase ? [
                    'id'             => $visaCase->id,
                    'application_no' => $visaCase->application_no,
                    'status'         => $visaCase->status,
                ] : null,
            ]);
        }

        $pricing = app(BookingPricing::class);
        $quote   = $pricing->quote(
            (float) $package->price * (int) $request->travelers,
            $customer,
            $request->input('coupon_code'),
            (int) $request->input('points_redeemed', 0),
        );

        $booking = Booking::create([
            'customer_id'    => $customer->id,
            'package_id'     => $package->id,
            'customer_name'  => $request->input('customer_name') ?: $customer->name,
            'customer_email' => $request->input('customer_email') ?: $customer->email,
            'customer_phone' => $request->input('customer_phone') ?: $customer->phone,
            'travel_date'    => $request->travel_date,
            'travelers'      => $request->travelers,
            'status'         => 'pending',
            'notes'          => $request->notes,
        ] + $pricing->columns($quote));

        // The coupon use and the points are only truly spent now the booking
        // exists; if either slipped away in between the booking is re-priced
        // and the customer is told which discount did not stick.
        $lost = $pricing->commit($booking, $quote);

        $visaCase = $request->boolean('needs_visa')
            ? app(OpenVisaCaseForBooking::class)($booking, $package, $customer)
            : null;

        Notification::notify($customer, 'Booking created',
            "Booking {$this->bookingInfo($booking)['reference']} is pending payment.", 'booking');

        return $this->responseWithSuccess(
            trim('Booking created. ' . implode(' ', $lost)),
            [
                'booking'         => $this->bookingInfo($booking->fresh()),
                'discount_notes'  => array_values(array_filter([
                    $quote['coupon_error'],
                    $quote['points_error'],
                    ...$lost,
                ])),
                'visa_application' => $visaCase ? [
                    'id'             => $visaCase->id,
                    'application_no' => $visaCase->application_no,
                    'status'         => $visaCase->status,
                ] : null,
            ],
            201
        );
    }

    /**
     * Price a tour before booking it — what the app's Book sheet calls when
     * a promo code is typed or the points slider moves.
     *
     * The same BookingPricing the booking is written with answers here, so
     * the figure quoted and the figure charged cannot drift apart.
     */
    public function quote(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can book.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'package_id'      => ['required', 'integer', 'exists:packages,id'],
            'travelers'       => ['required', 'integer', 'min:1', 'max:50'],
            'coupon_code'     => ['nullable', 'string', 'max:60'],
            'points_redeemed' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $package = Package::where('status', 'active')->find($request->package_id);
        if (! $package) {
            return $this->responseWithError('Tour not available.', [], 404);
        }

        $quote = app(BookingPricing::class)->preview(
            (float) $package->price * (int) $request->travelers,
            $customer,
            $request->input('coupon_code'),
            (int) $request->input('points_redeemed', 0),
        );

        return $this->responseWithSuccess('Quote ready.', ['quote' => $quote]);
    }

    /**
     * The logged-in customer's bookings (optional ?status= filter).
     */
    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers have bookings here.', [], 403);
        }

        $query = Booking::with('package')->where('customer_id', $customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->get()->map(fn (Booking $b) => $this->bookingInfo($b));

        return $this->responseWithSuccess('Bookings fetched.', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * A single booking (must belong to the customer).
     */
    public function show(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $booking = Booking::with('package')
            ->where('customer_id', $customer->id)
            ->find($id);

        if (! $booking) {
            return $this->responseWithError('Booking not found.', [], 404);
        }

        return $this->responseWithSuccess('Booking detail fetched.', [
            'booking' => $this->bookingInfo($booking),
        ]);
    }

    /**
     * Cancel a booking. If it was paid, what remains after the admin-configured
     * cancellation penalty is refunded to the wallet.
     *
     * Self-service cancellation is only allowed while the booking's travel
     * date is still at least booking_cancellation_window_hours away — see
     * App\Services\Booking\CancellationPolicy. A booking too close to (or
     * past) its travel date has to be cancelled by the office instead.
     */
    public function cancel(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $booking = Booking::where('customer_id', $customer->id)->find($id);
        if (! $booking) {
            return $this->responseWithError('Booking not found.', [], 404);
        }
        if ($booking->status === 'cancelled') {
            return $this->responseWithError('Booking already cancelled.', [], 409);
        }

        if (! CancellationPolicy::isWithinCancellableWindow($booking)) {
            $windowHours = CancellationPolicy::windowHours();

            $message = CancellationPolicy::hasTravelDatePassed($booking)
                ? 'This booking\'s travel date has already passed, so it can no longer be cancelled online. Please contact support.'
                : "Bookings can only be cancelled online at least {$windowHours} hour(s) before the travel date. Please contact support.";

            return $this->responseWithError($message, ['cancellation_window_hours' => $windowHours], 422);
        }

        $wasPaid      = $booking->status === 'paid';
        $refundResult = null;

        DB::transaction(function () use ($booking, $wasPaid, &$refundResult) {
            // A refund is its own record now: the invoice keeps its history and
            // the money going back is posted (Dr Refunds, Cr Customer Wallet)
            // instead of a wallet credit that the books never saw. Net of the
            // admin-configured cancellation penalty (0% keeps today's full
            // refund behaviour).
            if ($wasPaid) {
                $refundResult = CancellationPolicy::refundBooking(
                    $booking, 'Wallet', 'Booking #' . $booking->id . ' cancelled by customer'
                );
            }

            $booking->update([
                'status'           => 'cancelled',
                'cancellation_fee' => $refundResult['penalty_amount'] ?? null,
            ]);
        });

        $refundAmount   = (float) ($refundResult['refund_amount'] ?? 0);
        $penaltyAmount  = (float) ($refundResult['penalty_amount'] ?? 0);
        $penaltyPercent = (int) ($refundResult['penalty_percent'] ?? 0);
        $refunded       = $wasPaid && $refundAmount > 0.009;

        Notification::notify($customer, 'Booking cancelled',
            $refunded
                ? ($penaltyAmount > 0.009
                    ? "Booking #{$booking->id} cancelled; " . currency_symbol() . number_format($refundAmount, 2)
                        . ' refunded to your wallet (' . currency_symbol() . number_format($penaltyAmount, 2)
                        . " cancellation fee, {$penaltyPercent}%, retained)."
                    : "Booking #{$booking->id} cancelled; " . currency_symbol() . number_format($refundAmount, 2) . ' refunded to your wallet.')
                : "Booking #{$booking->id} has been cancelled.",
            'booking');

        return $this->responseWithSuccess('Booking cancelled.', [
            'booking'         => $this->bookingInfo($booking->fresh()),
            'refunded'        => $refunded,
            'refunded_amount' => $refundAmount,
            'penalty_amount'  => $penaltyAmount,
            'penalty_percent' => $penaltyPercent,
        ]);
    }

    /**
     * Rate the guide who led this booking's tour. One rating per customer per
     * assignment (DB unique); allowed once the tour is over.
     */
    public function rateGuide(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->responseWithError($validator->errors()->first(), [], 422);
        }

        $booking = Booking::with('package')
            ->where('customer_id', $customer->id)
            ->find($id);

        if (! $booking) {
            return $this->responseWithError('Booking not found.', [], 404);
        }

        $assignment = $this->bookingGuideAssignment($booking);

        if (! $assignment) {
            return $this->responseWithError('No guide was assigned to this tour.', [], 422);
        }

        if ($assignment->status !== 'completed' && ! $assignment->end_date->isPast()) {
            return $this->responseWithError('You can rate the guide after the tour ends.', [], 422);
        }

        $already = \App\Models\TourGuideRating::where('tour_guide_assignment_id', $assignment->id)
            ->where('customer_id', $customer->id)
            ->exists();

        if ($already) {
            return $this->responseWithError('You have already rated this guide.', [], 422);
        }

        \App\Models\TourGuideRating::create([
            'tour_guide_id'            => $assignment->tour_guide_id,
            'customer_id'              => $customer->id,
            'tour_guide_assignment_id' => $assignment->id,
            'rating'                   => (int) $request->rating,
            'comment'                  => $request->comment,
        ]);

        return $this->responseWithSuccess('Thanks for rating your guide.', [
            'booking' => $this->bookingInfo($booking->fresh()),
        ]);
    }
}
