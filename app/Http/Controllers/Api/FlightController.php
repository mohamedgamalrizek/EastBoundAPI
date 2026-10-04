<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\FlightBooking;
use App\Models\FlightRoute;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Flight bookings for the Customer app. Without a live GDS, this is a
 * request-to-book flow: the customer submits a flight request (route, date,
 * passenger); the agency prices it and issues the ticket (PNR/fare added later).
 */
class FlightController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer;

    /**
     * Published fare deals — the FlightRoute rows the website lists under
     * "Top flight deals", in the same order (public).
     */
    public function routes()
    {
        $routes = FlightRoute::active()->ordered()->get()
            ->map(fn (FlightRoute $r) => [
                'id'               => $r->id,
                'origin'           => $r->origin,
                'origin_code'      => $r->origin_code,
                'destination'      => $r->destination,
                'destination_code' => $r->destination_code,
                'airline'          => $r->airline,
                'trip_type'        => $r->trip_type,
                'fare'             => (float) $r->fare,
                'is_featured'      => (bool) $r->is_featured,
            ]);

        return $this->responseWithSuccess(___('mobile.flight_routes_fetched'), [
            'routes' => $routes,
        ]);
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError(___('mobile.forbidden'), [], 403);
        }

        $items = FlightBooking::where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn (FlightBooking $f) => $this->info($f));

        return $this->responseWithSuccess(___('mobile.flight_bookings_fetched'), ['flights' => $items]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError(___('mobile.only_customers_can_request_flights'), [], 403);
        }

        $validator = Validator::make($request->all(), [
            'route'          => ['required', 'string', 'max:120'], // e.g. DAC-DXB
            'flight_date'    => ['required', 'date', 'after_or_equal:today'],
            'passenger_name' => ['nullable', 'string', 'max:255'],
            'airline'        => ['nullable', 'string', 'max:120'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(___('mobile.validation_failed'), $validator->errors(), 422);
        }

        $flight = FlightBooking::create([
            'customer_id'    => $customer->id,
            'pnr'            => '',  // assigned by agency on ticketing
            'ticket_no'      => '',
            'passenger_name' => $request->input('passenger_name', $customer->name),
            'airline'        => $request->airline ?? '',
            'route'          => $request->route,
            'flight_date'    => $request->flight_date,
            'fare'           => 0,
            // Capitalised to match FlightRepository::STATUSES and the
            // FlightBooking::TRANSITIONS keys. A lowercase "pending" is in
            // neither, so canMoveTo() returned false for every target and the
            // desk could never confirm a request the app had filed.
            'status'         => 'Pending',
        ]);

        // ___() ignores its $replace argument, so the placeholder is swapped here.
        $notifyBody = str_replace(':route', $flight->route, ___('mobile.flight_request_received_body'));
        Notification::notify($customer, ___('mobile.flight_request_received_title'), $notifyBody, 'booking');

        return $this->responseWithSuccess(___('mobile.flight_request_submitted'), [
            'flight' => $this->info($flight),
        ], 201);
    }

    private function info(FlightBooking $f): array
    {
        return [
            'id'             => $f->id,
            'pnr'            => $f->pnr,
            'passenger_name' => $f->passenger_name,
            'airline'        => $f->airline,
            'route'          => $f->route,
            'flight_date'    => optional($f->flight_date)->toDateString(),
            'ticket_no'      => $f->ticket_no,
            'fare'           => (float) $f->fare,
            'status'         => $f->status,
        ];
    }
}
