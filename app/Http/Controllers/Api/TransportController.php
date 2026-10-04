<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Notification;
use App\Models\TransportBooking;
use App\Models\TransportService;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\StartsOnlinePayment;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Transport bookings (bus / train / launch / car / airport transfer) for the
 * Customer app. Like flights this is a request-to-book flow: the customer
 * submits the route and date, the agency confirms the vehicle and fare.
 */
class TransportController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer, StartsOnlinePayment;

    public const TYPES = ['Bus', 'Train', 'Launch', 'Car', 'Airport'];

    /** Vehicle types the app renders in its picker (public). */
    public function types()
    {
        return $this->responseWithSuccess(___('mobile.types_fetched'), [
            'types' => self::TYPES,
        ]);
    }

    /**
     * Active vehicle categories (Sedan, SUV, Coach…) — the same list the
     * admin's transport form offers, so the app's vehicle picker matches the
     * desk's (public).
     */
    public function vehicleCategories()
    {
        return $this->responseWithSuccess(___('mobile.vehicle_categories_fetched'), [
            'categories' => \App\Models\VehicleCategory::active()->ordered()->pluck('name'),
        ]);
    }

    /**
     * The agency's drivers — the same list the admin's transport form offers
     * (authenticated; driver names are staff data, not public).
     */
    public function drivers()
    {
        return $this->responseWithSuccess(___('mobile.drivers_fetched'), [
            'drivers' => \App\Models\Driver::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Public transport catalogue — the CMS products the website's Transport
     * page lists, with the same starting fare it shows: the cheapest fare
     * actually booked for that vehicle type, falling back to the configured
     * price, then to "request a quote" (a null fare).
     */
    public function services()
    {
        $bookedFrom = TransportBooking::selectRaw('type, MIN(fare) as from_fare')
            ->groupBy('type')
            ->pluck('from_fare', 'type');

        $services = TransportService::active()->ordered()->get()
            ->map(function (TransportService $s) use ($bookedFrom) {
                $booked = $s->vehicle_type ? ($bookedFrom[$s->vehicle_type] ?? null) : null;
                $from   = $booked ?: ((float) $s->price_from > 0 ? $s->price_from : null);

                return [
                    'id'           => $s->id,
                    'title'        => $s->title,
                    'icon'         => $s->icon,
                    'description'  => $s->description,
                    'from_fare'    => $from !== null ? (float) $from : null,
                    // A booked fare is a real trip total, so it carries no
                    // "/day" style unit — same rule the web page applies.
                    'price_unit'   => $booked ? '' : $s->price_unit,
                    'booking_type' => $s->booking_type,
                    'vehicle_type' => $s->vehicle_type,
                ];
            });

        return $this->responseWithSuccess(___('mobile.transport_services_fetched'), [
            'services' => $services,
        ]);
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError(___('mobile.forbidden'), [], 403);
        }

        $items = TransportBooking::where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn (TransportBooking $t) => $this->info($t));

        return $this->responseWithSuccess(___('mobile.transport_bookings_fetched'), [
            'transports' => $items,
        ]);
    }

    public function show(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError(___('mobile.forbidden'), [], 403);
        }

        $trip = TransportBooking::where('customer_id', $customer->id)->find($id);
        if (! $trip) {
            return $this->responseWithError(___('mobile.transport_booking_not_found'), [], 404);
        }

        return $this->responseWithSuccess(___('mobile.transport_booking_fetched'), [
            'transport' => $this->info($trip),
        ]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError(___('mobile.only_customers_can_book_transport'), [], 403);
        }

        $validator = Validator::make($request->all(), [
            'type'          => ['required', 'string', 'in:' . implode(',', self::TYPES)],
            'route'         => ['required', 'string', 'max:190'],
            'travel_date'   => ['required', 'date', 'after_or_equal:today'],
            'vehicle'       => ['nullable', 'string', 'max:120'],
            'customer_name' => ['nullable', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(___('mobile.validation_failed'), $validator->errors(), 422);
        }

        $trip = TransportBooking::create([
            'customer_id'   => $customer->id,
            'booking_no'    => 'TRP-' . strtoupper(substr(uniqid(), -6)),
            'type'          => $request->type,
            'customer_name' => $request->input('customer_name', $customer->name),
            'route'         => $request->route,
            'travel_date'   => $request->travel_date,
            'vehicle'       => $request->vehicle,
            'fare'          => 0, // priced by the agency on confirmation
            'status'        => 'Pending',
        ]);

        // ___() ignores its $replace argument, so the placeholders are swapped here.
        $notifyBody = str_replace(
            [':type', ':route'],
            [$trip->type, $trip->route],
            ___('mobile.transport_request_received_body')
        );
        Notification::notify($customer, ___('mobile.transport_request_received_title'), $notifyBody, 'booking');

        return $this->responseWithSuccess(___('mobile.transport_request_submitted'), [
            'transport' => $this->info($trip),
        ], 201);
    }

    /**
     * Pay the fare on a priced trip. A Pending request has fare 0 until the
     * agency prices it — nothing to pay yet. Marking it Paid makes
     * TransportBookingObserver raise the invoice and receipt in the method
     * used; a Wallet receipt debits the wallet as its other leg.
     */
    public function pay(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError(___('mobile.only_customers_can_pay'), [], 403);
        }

        $methods = ['cash' => 'Cash', 'bank' => 'Bank', 'card' => 'Card', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'wallet' => 'Wallet'];
        $method  = $methods[strtolower((string) $request->input('method', 'Card'))] ?? null;
        if (! $method) {
            return $this->responseWithError(___('mobile.unknown_payment_method'), [], 422);
        }

        $trip = TransportBooking::where('customer_id', $customer->id)->find($id);
        if (! $trip) {
            return $this->responseWithError(___('mobile.transport_booking_not_found'), [], 404);
        }
        if ($trip->status === 'Paid') {
            return $this->responseWithError(___('mobile.trip_already_paid'), [], 409);
        }
        if ($trip->status === 'Cancelled') {
            return $this->responseWithError(___('mobile.cancelled_trip_cannot_be_paid'), [], 409);
        }
        if ((float) $trip->fare <= 0) {
            return $this->responseWithError(___('mobile.trip_not_priced_yet'), [], 422);
        }

        // A live gateway collects the money and the callback settles the trip.
        try {
            if ($checkout = $this->startOnlinePayment($trip, $request->input('method'), (int) $customer->id)) {
                return $this->responseWithSuccess(___('mobile.complete_payment_to_confirm'), $checkout);
            }
        } catch (\RuntimeException $e) {
            return $this->responseWithError($e->getMessage(), [], 502);
        }

        $wallet = app(\App\Services\Accounting\CustomerWalletService::class);
        if ($method === 'Wallet' && $wallet->balance($customer->id) < (float) $trip->fare) {
            return $this->responseWithError(___('mobile.insufficient_wallet_balance'),
                ['balance' => $wallet->balance($customer->id)], 422);
        }

        // No gateway: a declaration, not a payment. The desk confirms it before
        // TransportBookingObserver raises the invoice and receipt.
        app(\App\Actions\RecordPaymentClaim::class)(
            $trip,
            $trip->booking_no,
            (string) ($trip->customer_name ?: $customer->name),
            (float) $trip->fare,
            $method
        );

        // ___() ignores its $replace argument, so the placeholder is swapped here.
        $notifyBody = str_replace(':booking_no', $trip->booking_no, ___('mobile.payment_submitted_body'));
        Notification::notify($customer, ___('mobile.payment_submitted_title'), $notifyBody, 'payment');

        return $this->responseWithSuccess(
            ___('mobile.payment_submitted_confirm'), [
            'transport' => $this->info($trip->fresh()),
            'awaiting_confirmation' => true,
            'wallet_balance' => $wallet->balance($customer->id),
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError(___('mobile.forbidden'), [], 403);
        }

        $trip = TransportBooking::where('customer_id', $customer->id)->find($id);
        if (! $trip) {
            return $this->responseWithError(___('mobile.transport_booking_not_found'), [], 404);
        }
        if (strtolower($trip->status) === 'cancelled') {
            return $this->responseWithError(___('mobile.already_cancelled'), [], 422);
        }

        $trip->update(['status' => 'Cancelled']);

        return $this->responseWithSuccess(___('mobile.transport_booking_cancelled'), [
            'transport' => $this->info($trip),
        ]);
    }

    private function info(TransportBooking $t): array
    {
        return [
            'id'            => $t->id,
            'booking_no'    => $t->booking_no,
            'type'          => $t->type,
            'customer_name' => $t->customer_name,
            'route'         => $t->route,
            'travel_date'   => optional($t->travel_date)->toDateString(),
            'vehicle'       => $t->vehicle,
            'fare'          => (float) $t->fare,
            'status'        => $t->status,
        ];
    }
}
