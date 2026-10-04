<?php

namespace App\Actions;

use App\Models\TransportBooking;
use App\Models\User;
use App\Traits\ResolvesCustomer;

/**
 * An agent sells a transport trip to their client.
 *
 * The trip itself is opened by the same action the website's transport
 * enquiry drives — OpenTransportBookingFromEnquiry — so the agent path does
 * not fork the booking logic: a Pending booking with fare 0, priced by the
 * desk on confirmation. What the agent adds is attribution: the client is
 * resolved to a customer record and the trip is stamped with the agent, who
 * earns a commission once the priced trip is actually paid.
 *
 * Shared by the agent portal and the mobile app so a sale is the same record
 * whichever surface the agent was sitting in front of.
 */
class CreateAgentTransportBooking
{
    use ResolvesCustomer;

    /**
     * @param  array  $data  client_name, client_phone, client_email, type,
     *                       direction, route, travel_date, vehicle
     */
    public function __invoke(User $agent, array $data): TransportBooking
    {
        // The client gets a customer record, exactly as the website's
        // transport enquiry gives one to a walk-in. Without it the trip is
        // orphaned: missing from the Customers list and from the client's
        // own portal.
        $client = $this->resolveCustomerFromContact([
            'customer_name'  => $data['client_name'],
            'customer_email' => $data['client_email'] ?? null,
            'customer_phone' => $data['client_phone'] ?? null,
        ], "Created from an agent transport booking ({$agent->name}).");

        // The enquiry action expects the contact under its own keys.
        $sale = [
            'type'        => $data['type'],
            'direction'   => $data['direction'] ?? null,
            'route'       => $data['route'],
            'travel_date' => $data['travel_date'],
            'vehicle'     => $data['vehicle'] ?? null,
            'driver_id'   => $data['driver_id'] ?? null,
            'fare'        => $data['fare'] ?? null,
            'name'        => $data['client_name'],
            'phone'       => $data['client_phone'] ?? null,
            'email'       => $data['client_email'] ?? null,
        ];

        return app(OpenTransportBookingFromEnquiry::class)($client, $sale, $agent);
    }
}
