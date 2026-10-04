<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\TransportBooking;
use App\Models\User;

/**
 * Turns a self-service transport request — the website's airport transfer /
 * car rental form — into a real Transport > Manage booking.
 *
 * It used to leave only a CRM lead, so pickup/dropoff, the requested vehicle
 * and the travel date were prose in a notes field and the trip never showed
 * up on the Transport desk's board at all. One shape for every channel: a
 * Pending booking with fare 0 — the same "priced by the agency on
 * confirmation" state the mobile app's own transport request already leaves
 * a fresh trip in (see Api\TransportController::store()) — so staff quote it
 * from Transport > Manage instead of re-keying an enquiry.
 */
class OpenTransportBookingFromEnquiry
{
    /**
     * @param  array  $data  type, route, travel_date, vehicle, name, phone,
     *                       email, details, plus whatever the form asked that
     *                       has no column of its own (time, with_driver,
     *                       return_date…). direction (Pickup/Drop) only means
     *                       something for an Airport type — every other type
     *                       leaves it null.
     * @param  ?User  $agent  when an agent sells the trip, the booking is
     *                       stamped with them (and later earns them a
     *                       commission once paid) and no CRM lead is raised —
     *                       an attributed sale is not a cold website enquiry.
     */
    public function __invoke(Customer $customer, array $data, ?User $agent = null): TransportBooking
    {
        // Re-submitting the form (or a double click) must not open a second
        // job for the same trip while the first is still awaiting a quote.
        // An agent's sale is scoped to that agent, so two agents selling the
        // same client the same trip each get their own record — and their
        // own commission — instead of silently sharing one.
        $existing = TransportBooking::where('customer_id', $customer->id)
            ->where('type', $data['type'])
            ->where('direction', $data['direction'] ?? null)
            ->where('route', $data['route'])
            ->where('travel_date', $data['travel_date'])
            ->whereIn('status', ['Pending', 'Booked', 'Confirmed'])
            ->when($agent, fn ($q) => $q->where('agent_id', $agent->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        $trip = TransportBooking::create([
            'customer_id'   => $customer->id,
            'agent_id'      => $agent?->id,
            'booking_no'    => 'TRP-' . strtoupper(substr(uniqid(), -6)),
            'type'          => $data['type'],
            'direction'     => $data['direction'] ?? null,
            'customer_name' => $data['name'] ?? $customer->name,
            'route'         => $data['route'],
            'travel_date'   => $data['travel_date'],
            'vehicle'       => $data['vehicle'] ?? null,
            'driver_id'     => $data['driver_id'] ?? null,
            // An agent may quote the client a fare up front (a budgeted
            // customer, an operator's rate); the desk still prices it when
            // the enquiry leaves it blank.
            'fare'          => (float) ($data['fare'] ?? 0),
            'status'        => 'Pending',
        ]);

        if (! $agent) {
            $this->lead($trip, $customer, $data);
        }

        $what = $trip->direction ? "{$trip->direction} — {$trip->type}" : $trip->type;
        Notification::notify($customer, 'Transport request received',
            "We are arranging your {$what} on {$trip->route}. We'll confirm the fare shortly.", 'booking');

        return $trip;
    }

    /**
     * Answers with no column of their own (pickup time, self-drive vs. with
     * driver, a rental's return date, free text) stay with the consultant as
     * a lead.
     */
    private function lead(TransportBooking $trip, Customer $customer, array $data): void
    {
        $extra = collect($data)
            ->only(['time', 'with_driver', 'return_date'])
            ->filter(fn ($v) => filled($v))
            ->map(fn ($v, $k) => ucwords(str_replace('_', ' ', $k)) . ': ' . $v)
            ->implode("\n");

        Lead::create([
            'name'     => $data['name'] ?? $customer->name,
            'phone'    => $data['phone'] ?? $customer->phone,
            'email'    => $data['email'] ?? $customer->email,
            'interest' => $trip->direction ? "{$trip->type} {$trip->direction}" : "{$trip->type} Transport",
            'source'   => 'Website',
            'stage'    => 'New',
            'notes'    => trim("[Website] Transport request {$trip->booking_no}.\n"
                . "Route: {$trip->route}\n"
                . $extra . "\n" . ($data['details'] ?? '')),
        ]);
    }
}
