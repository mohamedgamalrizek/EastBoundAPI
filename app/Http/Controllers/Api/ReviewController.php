<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Review;
use App\Services\ReviewService;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;
use App\Traits\ResolvesCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Traveller reviews.
 *
 * Reading approved reviews for a tour is public (the catalogue is). Writing
 * one is not: it takes a paid booking of the signed-in customer whose travel
 * date has passed, which ReviewService decides — the same rule the portal and
 * the website apply.
 */
class ReviewController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait, ResolvesCustomer;

    public function __construct(protected ReviewService $reviews) {}

    /** Approved reviews for one tour. Public. */
    public function index(Request $request, $packageId)
    {
        $package = Package::find($packageId);

        if (! $package) {
            return $this->responseWithError('Tour not found.', [], 404);
        }

        $reviews = $package->reviews()->approved()->with('customer')
            ->latest()
            ->paginate(min(50, max(5, (int) $request->input('per_page', 15))));

        return $this->responseWithSuccess('Reviews fetched.', [
            'reviews'       => $reviews->getCollection()->map(fn (Review $r) => $this->reviewInfo($r))->values(),
            'avg_rating'    => $package->avg_rating,
            'reviews_count' => $package->reviews_count,
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    /** The signed-in customer's own reviews, plus the trips still to review. */
    public function mine(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Customers only.', [], 403);
        }

        return $this->responseWithSuccess('Reviews fetched.', [
            'reviews' => $customer->reviews()->with('package')->latest()->get()
                ->map(fn (Review $r) => $this->reviewInfo($r) + [
                    'package_title' => $r->package?->title,
                ])->values(),
            // What to prompt on: paid, travelled, not yet reviewed.
            'awaiting' => $this->reviews->awaitingReview($customer)
                ->map(fn (Booking $b) => [
                    'booking_id'    => $b->id,
                    'reference'     => 'BKG-' . str_pad((string) $b->id, 5, '0', STR_PAD_LEFT),
                    'package_id'    => $b->package_id,
                    'package_title' => $b->package?->title,
                    'image_url'     => $b->package ? $this->imageUrl($b->package->image, 'pkg' . $b->package->id) : null,
                    'travel_date'   => optional($b->travel_date)->toDateString(),
                ])->values(),
        ]);
    }

    /** Write a review for one of the customer's own bookings. */
    public function store(Request $request, $bookingId)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Customers only.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:120'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError($validator->errors()->first(), $validator->errors(), 422);
        }

        $booking = Booking::where('customer_id', $customer->id)->find($bookingId);
        if (! $booking) {
            return $this->responseWithError('Booking not found.', [], 404);
        }

        if ($reason = $this->reviews->rejectionReason($booking, $customer)) {
            return $this->responseWithError($reason, [], 422);
        }

        $review = $this->reviews->create(
            $booking, $customer, (int) $request->rating, $request->title, $request->comment
        );

        return $this->responseWithSuccess(
            $this->reviews->autoApproves()
                ? 'Thanks — your review is now live.'
                : 'Thanks — your review will appear once it has been checked.',
            ['review' => $this->reviewInfo($review)],
            201
        );
    }
}
