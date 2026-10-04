<?php

namespace App\Http\Controllers\Api;

use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\HotelBooking;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\StartsOnlinePayment;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Hotel browsing + booking for the Customer app.
 */
class HotelController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait, ResolvesCustomer, StartsOnlinePayment;

    /**
     * Public hotel list. Filters match the website's search: ?city= (matched
     * against city, country or hotel name), ?stars= and ?search=. Results come
     * back featured-first, then by star rating, as on the web page.
     */
    public function index(Request $request)
    {
        $query = Hotel::where('status', 'active');

        if ($request->filled('city')) {
            $city = $request->city;
            $query->where(function ($w) use ($city) {
                $w->where('city', 'like', "%{$city}%")
                  ->orWhere('country', 'like', "%{$city}%")
                  ->orWhere('name', 'like', "%{$city}%");
            });
        }
        if ($request->filled('stars')) {
            $query->where('category', (int) $request->stars);
        }
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('city', 'like', "%{$request->search}%"));
        }

        $hotels = $query->orderByDesc('is_featured')->orderByDesc('category')->get()
            ->map(fn (Hotel $h) => $this->hotelCard($h));

        return $this->responseWithSuccess('Hotels fetched.', [
            'hotels' => $hotels,
            // Powers the destination picker, same list the web datalist uses.
            'cities' => Hotel::active()->distinct()->orderBy('city')->pluck('city'),
        ]);
    }

    /** Hotel detail with rooms. */
    public function show($id)
    {
        $hotel = Hotel::where('status', 'active')->with('rooms')->find($id);
        if (! $hotel) {
            return $this->responseWithError('Hotel not found.', [], 404);
        }

        return $this->responseWithSuccess('Hotel detail fetched.', [
            'hotel' => array_merge($this->hotelCard($hotel), [
                'rooms' => $hotel->rooms
                    ->where('status', '!=', 'sold-out')
                    ->map(fn (HotelRoom $r) => [
                        'id'              => $r->id,
                        'room_type'       => $r->room_type,
                        'capacity'        => (int) $r->capacity,
                        'rate_per_night'  => (float) $r->rate_per_night,
                        'available_rooms' => (int) $r->available_rooms,
                    ])->values(),
            ]),
        ]);
    }

    /** Book a hotel room (auth, customer). */
    public function book(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can book.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'hotel_id'      => ['required', 'integer', 'exists:hotels,id'],
            'hotel_room_id' => ['required', 'integer', 'exists:hotel_rooms,id'],
            'check_in'      => ['required', 'date', 'after_or_equal:today'],
            'check_out'     => ['required', 'date', 'after:check_in'],
            'guest_name'    => ['nullable', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $room = HotelRoom::where('hotel_id', $request->hotel_id)->find($request->hotel_room_id);
        if (! $room) {
            return $this->responseWithError('Room not found for this hotel.', [], 404);
        }

        $nights = Carbon::parse($request->check_in)->diffInDays(Carbon::parse($request->check_out));
        $nights = max(1, (int) $nights);
        $amount = $nights * (float) $room->rate_per_night;

        $booking = HotelBooking::create([
            'booking_no'    => 'HTL-' . strtoupper(substr(uniqid(), -6)),
            'hotel_id'      => $request->hotel_id,
            'customer_id'   => $customer->id,
            'hotel_room_id' => $room->id,
            'guest_name'    => $request->input('guest_name', $customer->name),
            'check_in'      => $request->check_in,
            'check_out'     => $request->check_out,
            'nights'        => $nights,
            'amount'        => $amount,
            // 'Booked' = tentative, matching the back-office vocabulary; the
            // desk confirms it (raising the invoice) and takes payment.
            'status'        => 'Booked',
        ]);

        Notification::notify($customer, 'Hotel booking created',
            "Booking {$booking->booking_no} ({$nights} night(s)) is pending.", 'booking');

        return $this->responseWithSuccess('Hotel booked.', [
            'booking' => $this->hotelBookingInfo($booking->fresh(['hotel', 'hotelRoom'])),
        ], 201);
    }

    /**
     * Pay for a hotel booking. Record-payment like the tour path: marking the
     * row Paid makes HotelBookingObserver raise the invoice and receipt in the
     * method used; a Wallet receipt debits the wallet as its other leg.
     */
    public function pay(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers can pay.', [], 403);
        }

        $methods = ['cash' => 'Cash', 'bank' => 'Bank', 'card' => 'Card', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'wallet' => 'Wallet'];
        $method  = $methods[strtolower((string) $request->input('method', 'Card'))] ?? null;
        if (! $method) {
            return $this->responseWithError('Unknown payment method.', [], 422);
        }

        $booking = HotelBooking::where('customer_id', $customer->id)->find($id);
        if (! $booking) {
            return $this->responseWithError('Hotel booking not found.', [], 404);
        }
        if ($booking->status === 'Paid') {
            return $this->responseWithError('Booking is already paid.', [], 409);
        }
        if ($booking->status === 'Cancelled') {
            return $this->responseWithError('Cancelled booking cannot be paid.', [], 409);
        }
        if ((float) $booking->amount <= 0) {
            return $this->responseWithError('Nothing to pay on this booking.', [], 422);
        }

        // A live gateway collects the money and the callback settles the stay.
        try {
            if ($checkout = $this->startOnlinePayment($booking, $request->input('method'), (int) $customer->id)) {
                return $this->responseWithSuccess('Complete the payment to confirm this booking.', $checkout);
            }
        } catch (\RuntimeException $e) {
            return $this->responseWithError($e->getMessage(), [], 502);
        }

        $wallet = app(\App\Services\Accounting\CustomerWalletService::class);
        if ($method === 'Wallet' && $wallet->balance($customer->id) < (float) $booking->amount) {
            return $this->responseWithError('Insufficient wallet balance. Please top up.',
                ['balance' => $wallet->balance($customer->id)], 422);
        }

        // No gateway: a declaration, not a payment. Marking it Paid would make
        // HotelBookingObserver raise the invoice and receipt for money nobody
        // has received — the desk confirms it instead, as on the web portal.
        app(\App\Actions\RecordPaymentClaim::class)(
            $booking,
            $booking->booking_no,
            (string) ($booking->guest_name ?: $customer->name),
            (float) $booking->amount,
            $method
        );

        Notification::notify($customer, 'Payment submitted',
            "We have your payment for {$booking->booking_no} and will confirm it shortly.", 'payment');

        return $this->responseWithSuccess(
            'Payment submitted — we will confirm it shortly and update your booking.', [
            'booking' => $this->hotelBookingInfo($booking->fresh(['hotel', 'hotelRoom'])),
            'awaiting_confirmation' => true,
            'wallet_balance' => $wallet->balance($customer->id),
        ]);
    }

    /** Customer's hotel bookings. */
    public function myBookings(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $items = HotelBooking::with(['hotel', 'hotelRoom'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn (HotelBooking $b) => $this->hotelBookingInfo($b));

        return $this->responseWithSuccess('Hotel bookings fetched.', ['bookings' => $items]);
    }

    private function hotelCard(Hotel $h): array
    {
        return [
            'id'              => $h->id,
            'name'            => $h->name,
            'city'            => $h->city,
            'country'         => $h->country,
            'stars'           => (int) $h->category,
            'price_per_night' => (float) $h->price_per_night,
            'description'     => $h->description,
            'is_featured'     => (bool) $h->is_featured,
            'rooms_count'     => (int) $h->rooms_count,
            // The hotel's own photo, like the website — the placeholder is only
            // a fallback now, not the only thing the app ever saw.
            'image_url'       => $this->imageUrl($h->image, 'hotel' . $h->id),
        ];
    }

    private function hotelBookingInfo(HotelBooking $b): array
    {
        return [
            'id'         => $b->id,
            'booking_no' => $b->booking_no,
            'hotel'      => $b->hotel?->name,
            // hotel_bookings has no room_type column; the booked room carries it.
            'room_type'  => $b->hotelRoom?->room_type,
            'check_in'   => optional($b->check_in)->toDateString(),
            'check_out'  => optional($b->check_out)->toDateString(),
            'nights'     => (int) $b->nights,
            'amount'     => (float) $b->amount,
            'status'     => $b->status,
        ];
    }
}
