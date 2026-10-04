<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use App\Traits\ResolvesCustomer;

/**
 * An agent sells a package to their client.
 *
 * This is the commission model: the agent books at the published price, the
 * client pays the agency, and the agent's cut is worked out by
 * AgentSettlementService once the booking is actually paid. So the fare is
 * quoted from the catalogue here and never accepted from the caller — an
 * agent has no price of their own to set.
 *
 * Shared by the agent portal and the mobile app so a sale is the same record
 * whichever surface the agent was sitting in front of.
 */
class CreateAgentBooking
{
    use ResolvesCustomer;

    /**
     * @param  array  $data  client_name, client_phone, client_email,
     *                       travel_date, travelers
     */
    public function __invoke(User $agent, Package $package, array $data): Booking
    {
        $travelers = (int) ($data['travelers'] ?? 1) ?: 1;

        // The client gets a customer record, exactly as the website's booking
        // form gives one to a walk-in. Without it the booking is orphaned:
        // missing from the Customers list and from the client's own portal,
        // and the invoice it raises has no customer to belong to.
        $client = $this->resolveCustomerFromContact([
            'customer_name'  => $data['client_name'],
            'customer_email' => $data['client_email'] ?? null,
            'customer_phone' => $data['client_phone'] ?? null,
        ], "Created from an agent booking ({$agent->name}).");

        // Re-submitting the form must not open a second booking for the same
        // trip while the first is still pending — each one invoices separately.
        $existing = Booking::where('agent_id', $agent->id)
            ->where('customer_id', $client->id)
            ->where('package_id', $package->id)
            ->whereDate('travel_date', $data['travel_date'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return $existing;
        }

        return Booking::create([
            'agent_id'       => $agent->id,
            'customer_id'    => $client->id,
            'package_id'     => $package->id,
            'customer_name'  => $data['client_name'],
            'customer_email' => $data['client_email'] ?? null,
            'customer_phone' => $data['client_phone'] ?? null,
            'travel_date'    => $data['travel_date'],
            'travelers'      => $travelers,
            'amount'         => (float) $package->price * $travelers,
            'status'         => 'pending',
            'notes'          => 'Booked by agent ' . $agent->name . '.',
        ]);
    }
}
