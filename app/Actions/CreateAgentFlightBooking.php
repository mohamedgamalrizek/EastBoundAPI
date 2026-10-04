<?php

namespace App\Actions;

use App\Models\FlightBooking;
use App\Models\User;
use App\Traits\ResolvesCustomer;
use Illuminate\Support\Str;

/**
 * An agent sells a flight to their client.
 *
 * Without a live GDS this is the same request-to-book flow the customer app
 * drives: the request opens Pending, and the desk prices it and issues the
 * ticket (PNR, ticket number, fare). What the agent adds is attribution — the
 * client is resolved to a customer record and the ticket is stamped with the
 * agent, who earns a commission once the desk has ticketed the sale.
 *
 * Shared by the agent portal and the mobile app so a sale is the same record
 * whichever surface the agent was sitting in front of.
 */
class CreateAgentFlightBooking
{
    use ResolvesCustomer;

    /**
     * @param  array  $data  client_name, client_phone, client_email, route,
     *                       flight_date, passenger_name, airline
     */
    public function __invoke(User $agent, array $data): FlightBooking
    {
        // The client gets a customer record, exactly as the website's flight
        // form gives one to a walk-in. Without it the request is orphaned:
        // missing from the Customers list and from the client's own portal.
        $client = $this->resolveCustomerFromContact([
            'customer_name'  => $data['client_name'],
            'customer_email' => $data['client_email'] ?? null,
            'customer_phone' => $data['client_phone'] ?? null,
        ], "Created from an agent flight booking ({$agent->name}).");

        // Re-submitting the form must not file a second request for the same
        // passenger and route while the first is still un-worked.
        $existing = FlightBooking::where('agent_id', $agent->id)
            ->where('customer_id', $client->id)
            ->where('route', $data['route'])
            ->whereDate('flight_date', $data['flight_date'])
            ->where('passenger_name', $data['passenger_name'])
            ->where('status', 'Pending')
            ->first();

        if ($existing) {
            return $existing;
        }

        // pnr is unique on the table, and a pending request has no real PNR
        // yet. The customer app writes '' here — fine for its first request,
        // but a second pending row would violate the unique index. A REQ-
        // placeholder keeps every pending request distinct until the desk
        // replaces it with the airline's PNR on ticketing.
        return FlightBooking::create([
            'customer_id'    => $client->id,
            'agent_id'       => $agent->id,
            'pnr'            => 'REQ-' . strtoupper(Str::random(8)),
            'ticket_no'      => null,
            'passenger_name' => $data['passenger_name'],
            'airline'        => $data['airline'] ?? '',
            'route'          => $data['route'],
            'flight_date'    => $data['flight_date'],
            'fare'           => 0,
            'status'         => 'Pending',
        ]);
    }
}
