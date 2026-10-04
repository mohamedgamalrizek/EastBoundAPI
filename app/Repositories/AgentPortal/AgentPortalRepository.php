<?php

namespace App\Repositories\AgentPortal;

use App\Models\AgentCommission;
use App\Models\AgentInvoice;
use App\Models\AgentWalletTransaction;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\AgentWithdrawal;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\SupportTicket;
use App\Models\TransportBooking;
use App\Models\VehicleCategory;
use App\Repositories\Transport\TransportRepository;
use App\Services\Accounting\AgentSettlementService;
use App\Traits\ReturnFormatTrait;
use App\Repositories\AgentPortal\AgentPortalInterface;

class AgentPortalRepository implements AgentPortalInterface
{
    use ReturnFormatTrait;

    /**
     * The logged-in agent. Every query below is filtered by this id — an agent
     * must only ever see their own bookings, commissions, wallet and invoices,
     * never the whole agency's.
     *
     * `agent_id` on bookings / commissions / wallet / invoices is a User id
     * (the User whose role is "Agent").
     */
    protected function currentAgentId(): ?int
    {
        return auth()->id();
    }

    public function dashboard()
    {
        $agentId = $this->currentAgentId();

        return [
            'sales'      => (float) Booking::where('agent_id', $agentId)->sum('amount'),
            'bookings'   => Booking::where('agent_id', $agentId)->count(),
            'commission' => (float) AgentCommission::where('agent_id', $agentId)->sum('amount'),
            'wallet'     => $this->walletBalance($agentId),
            'recent'     => Booking::where('agent_id', $agentId)->latest()->take(8)->get(),
        ];
    }

    public function bookings()
    {
        return [
            'bookings' => Booking::where('agent_id', $this->currentAgentId())->latest()->get(),
        ];
    }

    /** The catalogue the agent sells from — the same active packages the app lists. */
    public function bookingFormData()
    {
        return [
            'packages' => Package::where('status', 'active')
                ->orderBy('title')
                ->get(['id', 'title', 'destination', 'price']),
        ];
    }

    /**
     * Sell a package to a client. Goes through the same action the app's
     * "New Booking" uses, so the two surfaces cannot drift: the fare is quoted
     * from the catalogue, the client gets a customer record, and a repeated
     * submit returns the booking already open rather than a second one.
     */
    public function storeBooking($request)
    {
        $package = Package::where('status', 'active')->find($request->package_id);

        if (! $package) {
            return $this->responseWithError('That tour is not available.');
        }

        $booking = app(\App\Actions\CreateAgentBooking::class)(
            auth()->user(),
            $package,
            $request->validated()
        );

        return $this->responseWithSuccess(
            "Booking BKG-" . str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT)
            . " raised for {$booking->customer_name}.",
            ['booking' => $booking]
        );
    }

    /** The hotels and rooms the agent sells — the same active inventory the app lists. */
    public function hotelFormData()
    {
        return [
            'hotels' => Hotel::where('status', 'active')->orderBy('name')
                ->get(['id', 'name', 'city', 'country']),
            'rooms'  => HotelRoom::orderBy('room_type')
                ->get(['id', 'hotel_id', 'room_type', 'rate_per_night']),
        ];
    }

    /**
     * Sell a hotel stay to a client. Same action the app uses, so the two
     * surfaces cannot drift: the price is quoted server-side from the room's
     * published rate, the client gets a customer record, and a repeated submit
     * returns the stay already open rather than a second one.
     */
    public function storeHotelBooking($request)
    {
        $hotel = Hotel::where('status', 'active')->find($request->hotel_id);

        if (! $hotel) {
            return $this->responseWithError('That hotel is not available.');
        }

        $room = HotelRoom::where('hotel_id', $hotel->id)->find($request->hotel_room_id);

        if (! $room) {
            return $this->responseWithError('That room is not available at this hotel.');
        }

        $stay = app(\App\Actions\CreateAgentHotelBooking::class)(
            auth()->user(),
            $hotel,
            $room,
            $request->validated()
        );

        return $this->responseWithSuccess(
            "Hotel stay {$stay->booking_no} raised for {$stay->guest_name}.",
            ['booking' => $stay]
        );
    }

    /**
     * The published fare deals plus the airports they touch. The From/To
     * pickers are select2 searchable lists over the same cities the admin's
     * flight form offers, so the agent picks a route the way the desk does.
     */
    public function flightFormData()
    {
        $routes = \App\Models\FlightRoute::active()->ordered()
            ->get(['origin_code', 'destination_code', 'origin', 'destination', 'airline']);

        return [
            'routes' => $routes,
            'cities' => $routes
                ->flatMap(fn (\App\Models\FlightRoute $r) => [
                    (object) ['city' => $r->origin, 'code' => $r->origin_code],
                    (object) ['city' => $r->destination, 'code' => $r->destination_code],
                ])
                ->unique('city')
                ->sortBy('city')
                ->values(),
        ];
    }

    /**
     * Sell a flight to a client. Same action the app uses, so the two
     * surfaces cannot drift: the request opens Pending at fare 0 (the desk
     * prices it and issues the ticket), the client gets a customer record,
     * and the agent is stamped so a ticketed sale earns them their
     * commission.
     */
    public function storeFlightBooking($request)
    {
        $flight = app(\App\Actions\CreateAgentFlightBooking::class)(
            auth()->user(),
            $request->validated()
        );

        return $this->responseWithSuccess(
            "Flight request for {$flight->passenger_name} ({$flight->route}) filed.",
            ['booking' => $flight]
        );
    }

    /** The transport catalogue the agent sells — the same lists the admin's
     *  transport form offers, so a trip filed by an agent matches one filed
     *  by the desk.
     */
    public function transportFormData()
    {
        return [
            'types'             => TransportRepository::TYPES,
            'directions'        => TransportRepository::DIRECTIONS,
            'vehicleCategories' => VehicleCategory::active()->ordered()->pluck('name'),
            'drivers'           => Driver::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Sell a transport trip to a client. Same action the app uses, so the two
     * surfaces cannot drift: the trip opens Pending with fare 0 (the desk
     * prices it), the client gets a customer record, and the agent is stamped
     * so a paid trip earns them their commission.
     */
    public function storeTransportBooking($request)
    {
        $trip = app(\App\Actions\CreateAgentTransportBooking::class)(
            auth()->user(),
            $request->validated()
        );

        return $this->responseWithSuccess(
            "Transport trip {$trip->booking_no} opened for {$trip->customer_name}.",
            ['booking' => $trip]
        );
    }

    /**
     * One booking, with what the agency has invoiced on it and what the agent
     * earns — scoped to the agent, so another agent's id in the URL is a 404
     * rather than someone else's client.
     */
    public function booking($id)
    {
        $agentId = $this->currentAgentId();

        $booking = Booking::with('package')
            ->where('agent_id', $agentId)
            ->findOrFail($id);

        return [
            'booking'    => $booking,
            'invoice'    => Invoice::where('booking_id', $booking->id)->first(),
            'commission' => AgentCommission::where('agent_id', $agentId)
                                ->where('booking_id', $booking->id)
                                ->first(),
        ];
    }

    /**
     * Amend a pending booking. The amount is re-quoted from the package, never
     * taken from the form, so an agent cannot price their own sale.
     */
    public function updateBooking($request, $id)
    {
        $booking = Booking::where('agent_id', $this->currentAgentId())->findOrFail($id);

        if ($booking->status !== 'pending') {
            return $this->responseWithError("A {$booking->status} booking can only be changed by the office.");
        }

        $package = $booking->package_id ? Package::find($booking->package_id) : null;

        $booking->update([
            'travel_date' => $request->travel_date,
            'travelers'   => $request->travelers,
            'amount'      => $package
                ? (float) $package->price * (int) $request->travelers
                : $booking->amount,
        ]);

        return $this->responseWithSuccess('Booking updated.');
    }

    /**
     * Cancel a pending booking. A paid one is left to the office: refunding it
     * moves money and reverses a commission.
     */
    public function cancelBooking($id)
    {
        $booking = Booking::where('agent_id', $this->currentAgentId())->findOrFail($id);

        if ($booking->status === 'cancelled') {
            return $this->responseWithError('Booking already cancelled.');
        }

        if ($booking->status !== 'pending') {
            return $this->responseWithError("A {$booking->status} booking has to be cancelled by the office.");
        }

        $booking->update(['status' => 'cancelled']);

        return $this->responseWithSuccess("Booking #{$booking->id} cancelled.");
    }

    /** The agent's own clients — customers they have booked for. */
    public function customers()
    {
        $agentId = $this->currentAgentId();

        return [
            'customers' => Customer::whereHas('bookings', fn ($q) => $q->where('agent_id', $agentId))
                ->latest()
                ->get(),
        ];
    }

    /**
     * A visa document, only when its application belongs to one of this
     * agent's own customers (the same "has a booking with my agent_id" test
     * customers() uses above) — an agent has no business opening a document
     * for a customer they never booked for.
     */
    public function ownedVisaDocument($id): ?\App\Models\VisaDocument
    {
        $agentId = $this->currentAgentId();

        return \App\Models\VisaDocument::whereHas(
            'visaApplication.customer',
            fn ($q) => $q->whereHas('bookings', fn ($b) => $b->where('agent_id', $agentId))
        )->find($id);
    }

    public function commissions()
    {
        $agentId = $this->currentAgentId();
        $mine    = fn () => AgentCommission::where('agent_id', $agentId);

        return [
            'earned'      => (float) $mine()->sum('amount'),
            'pending'     => (float) $mine()->where('status', 'pending')->sum('amount'),
            // 'paid' means credited to the wallet — it is withdrawable, and
            // leaves the agency only when a payout is paid.
            'paid'        => (float) $mine()->where('status', 'paid')->sum('amount'),
            'rate'        => (float) ($mine()->avg('rate') ?? 0),
            'commissions' => $mine()->latest('earned_on')->latest('id')->get(),
        ];
    }

    /**
     * Wallet + payouts. `available` is what the agent may actually ask for:
     * the balance less anything already requested and not yet paid, so the
     * same commission cannot be claimed twice.
     */
    public function wallet()
    {
        $agentId    = $this->currentAgentId();
        $settlement = app(AgentSettlementService::class);
        $summary    = $settlement->summary($agentId);

        return array_merge($summary, [
            'transactions' => AgentWalletTransaction::where('agent_id', $agentId)
                                ->latest('txn_date')->latest('id')->get(),
            'withdrawals'  => AgentWithdrawal::where('agent_id', $agentId)
                                ->latest('requested_on')->latest('id')->get(),
            'methods'      => AgentWithdrawal::METHODS,
        ]);
    }

    public function transactions()
    {
        return [
            'transactions' => AgentWalletTransaction::where('agent_id', $this->currentAgentId())
                ->latest('txn_date')->latest('id')->get(),
        ];
    }

    public function invoices()
    {
        return [
            'invoices' => AgentInvoice::where('agent_id', $this->currentAgentId())
                ->latest('issued_on')->latest('id')->get(),
        ];
    }

    public function reports()
    {
        $agentId = $this->currentAgentId();

        return [
            'totalSales' => (float) Booking::where('agent_id', $agentId)->sum('amount'),
            'bookings'   => Booking::where('agent_id', $agentId)->count(),
            'commission' => (float) AgentCommission::where('agent_id', $agentId)->sum('amount'),
            'customers'  => Customer::whereHas('bookings', fn ($q) => $q->where('agent_id', $agentId))->count(),
            'byStatus'   => Booking::where('agent_id', $agentId)
                                ->selectRaw('status, count(*) as total')
                                ->groupBy('status')->pluck('total', 'status'),
            'invoiceByStatus' => AgentInvoice::where('agent_id', $agentId)
                                ->selectRaw('status, count(*) as total')
                                ->groupBy('status')->pluck('total', 'status'),
        ];
    }

    /**
     * Tickets assigned to this agent, the ones they opened themselves, plus
     * any raised by their own clients.
     */
    public function support()
    {
        $agentId     = $this->currentAgentId();
        $customerIds = Booking::where('agent_id', $agentId)
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id');

        return [
            'tickets' => SupportTicket::where(fn ($q) => $q
                    ->where('assigned_to', $agentId)
                    ->orWhere('raised_by', $agentId)
                    ->orWhereIn('customer_id', $customerIds))
                ->latest()
                ->get(),
            'priorities' => \App\Repositories\Support\SupportRepository::PRIORITIES,
        ];
    }

    /** Running wallet balance = balance_after on the agent's newest transaction. */
    private function walletBalance(?int $agentId): float
    {
        $latest = AgentWalletTransaction::where('agent_id', $agentId)
            ->orderByDesc('id')
            ->first();

        return $latest ? (float) $latest->balance_after : 0.0;
    }
}
