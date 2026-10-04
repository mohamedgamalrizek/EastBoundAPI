<?php

namespace App\Repositories\Booking;

use App\Models\Booking;
use App\Services\Booking\BookingPricing;
use App\Traits\ReturnFormatTrait;
use App\Repositories\Booking\BookingInterface;

class BookingRepository implements BookingInterface
{
    use ReturnFormatTrait;

    protected $model;

    public function __construct(Booking $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model::with(['package', 'agent'])->orderByDesc('id')->get();
    }

    public function get($id)
    {
        return $this->model::find($id);
    }

    public function store($request)
    {
        try {
            $pricing = app(BookingPricing::class);
            $quote   = $pricing->quote((float) $request->amount, null, $request->coupon_code);

            $booking = $this->model::create($this->data($request) + $pricing->columns($quote));

            $lost = $pricing->commit($booking, $quote);

            return $this->responseWithSuccess(trim(
                ___('alert.successfully_added') . ' ' . ($quote['coupon_error'] ?? '') . ' ' . implode(' ', $lost)
            ));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function update($request, $id)
    {
        try {
            $booking = $this->model::findOrFail($id);
            $pricing = app(BookingPricing::class);

            // The desk types a gross amount; the coupon comes off it. Only
            // re-price when the code or the amount actually moved, so simply
            // re-saving a booking to change its status cannot claim a second
            // use of the same coupon.
            $typed   = (float) $request->amount;
            $changed = trim((string) $request->coupon_code) !== (string) $booking->coupon_code
                || abs($typed - $booking->grossAmount()) > 0.009;

            if (! $changed) {
                // data() no longer carries `amount`, so the priced figure stands.
                $booking->update($this->data($request));

                return $this->responseWithSuccess(___('alert.successfully_updated'));
            }

            // Whatever the previous code entitled them to is given back before
            // the new one is claimed, otherwise editing a booking twice burns
            // two uses for one sale. Only the coupon: points the customer
            // redeemed themselves are carried over by requote().
            $pricing->releaseCoupon($booking);

            $quote = $pricing->requote($booking, $typed, $request->coupon_code);
            $booking->update($this->data($request) + $pricing->columns($quote));

            $lost = $pricing->commit($booking, $quote);

            return $this->responseWithSuccess(trim(
                ___('alert.successfully_updated') . ' ' . ($quote['coupon_error'] ?? '') . ' ' . implode(' ', $lost)
            ));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function delete($id)
    {
        try {
            $this->model::findOrFail($id)->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    private function data($request): array
    {
        // The booking links to the customer by id; name/email/phone are kept as
        // a snapshot of the customer at booking time (they may be edited later
        // in the CRM without rewriting historical bookings).
        $customer = \App\Models\Customer::find($request->customer_id);

        return [
            'package_id'     => $request->package_id,
            'customer_id'    => $customer?->id,
            // Who sold it. Without this the booking never reaches the agent's
            // portal — their bookings, customers, dashboard and reports all
            // filter on it.
            'agent_id'       => $request->agent_id ?: null,
            'customer_name'  => $customer?->name,
            'customer_email' => $request->customer_email ?: $customer?->email,
            'customer_phone' => $request->customer_phone ?: $customer?->phone,
            'travel_date'    => $request->travel_date,
            'travelers'      => $request->travelers,
            // `amount` is not set here: it is whatever the coupon left of the
            // figure the desk typed, and comes from BookingPricing::columns().
            'status'         => $request->status,
            'payment_method' => $request->payment_method,
            'notes'          => $request->notes,
        ];
    }
}
