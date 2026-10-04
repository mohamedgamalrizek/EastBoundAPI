<?php

namespace App\Actions;

use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Models\User;
use App\Traits\ResolvesCustomer;
use Illuminate\Support\Carbon;

/**
 * An agent sells a hotel stay to their client.
 *
 * This is the commission model: the agent books at the published rate, the
 * client pays the agency, and the agent's cut is worked out by
 * AgentSettlementService once the stay is actually paid. So the price is
 * quoted here — nights × the room's rate per night — and never accepted from
 * the caller; an agent has no price of their own to set.
 *
 * Shared by the agent portal and the mobile app so a sale is the same record
 * whichever surface the agent was sitting in front of.
 */
class CreateAgentHotelBooking
{
    use ResolvesCustomer;

    /**
     * @param  array  $data  client_name, client_phone, client_email,
     *                       check_in, check_out
     */
    public function __invoke(User $agent, Hotel $hotel, HotelRoom $room, array $data): HotelBooking
    {
        // The client gets a customer record, exactly as the website's hotel
        // form gives one to a walk-in. Without it the stay is orphaned:
        // missing from the Customers list and from the client's own portal.
        $client = $this->resolveCustomerFromContact([
            'customer_name'  => $data['client_name'],
            'customer_email' => $data['client_email'] ?? null,
            'customer_phone' => $data['client_phone'] ?? null,
        ], "Created from an agent hotel booking ({$agent->name}).");

        $nights = max(1, (int) Carbon::parse($data['check_in'])
            ->diffInDays(Carbon::parse($data['check_out'])));

        // Re-submitting the form must not open a second stay for the same
        // room and dates while the first is still tentative — each one
        // invoices separately.
        $existing = HotelBooking::where('agent_id', $agent->id)
            ->where('customer_id', $client->id)
            ->where('hotel_room_id', $room->id)
            ->whereDate('check_in', $data['check_in'])
            ->whereDate('check_out', $data['check_out'])
            ->where('status', 'Booked')
            ->first();

        if ($existing) {
            return $existing;
        }

        return HotelBooking::create([
            'booking_no'    => 'HTL-A' . strtoupper(substr(uniqid(), -6)),
            'hotel_id'      => $hotel->id,
            'hotel_room_id' => $room->id,
            'customer_id'   => $client->id,
            'agent_id'      => $agent->id,
            'guest_name'    => $data['client_name'],
            'check_in'      => $data['check_in'],
            'check_out'     => $data['check_out'],
            'nights'        => $nights,
            'amount'        => (float) $room->rate_per_night * $nights,
            // 'Booked' = tentative, matching the back-office vocabulary; the
            // desk confirms it (raising the invoice) and takes payment.
            'status'        => 'Booked',
        ]);
    }
}
