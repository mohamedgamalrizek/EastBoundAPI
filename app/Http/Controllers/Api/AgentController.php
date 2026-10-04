<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateAgentBooking;
use App\Actions\CreateAgentFlightBooking;
use App\Actions\CreateAgentHotelBooking;
use App\Actions\CreateAgentTransportBooking;
use App\Models\User;
use App\Models\Package;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\AgentCommission;
use App\Models\AgentInvoice;
use App\Models\AgentWalletTransaction;
use App\Models\Invoice;
use App\Models\SupportTicket;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;
use App\Traits\ResolvesCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Agent (B2B) endpoints — dashboard, wallet, commissions, invoices, reports
 * and support, mirroring the web agent portal.
 * Scoped to the authenticated agent (a User whose role is "Agent").
 */
class AgentController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait, ResolvesCustomer;

    public function dashboard(Request $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $commissions = AgentCommission::where('agent_id', $agent->id);

        $totalCommission   = (clone $commissions)->sum('amount');
        $paidCommission    = (clone $commissions)->where('status', 'paid')->sum('amount');
        $pendingCommission = (clone $commissions)->where('status', '!=', 'paid')->sum('amount');

        $bookings = Booking::where('agent_id', $agent->id);

        return $this->responseWithSuccess('Agent dashboard.', [
            'agent' => [
                'id'    => $agent->id,
                'name'  => $agent->name,
                'email' => $agent->email,
                'phone' => $agent->phone,
            ],
            'wallet_balance'     => $this->walletBalance($agent->id),
            'total_commission'   => (float) $totalCommission,
            'paid_commission'    => (float) $paidCommission,
            'pending_commission' => (float) $pendingCommission,
            'commission_count'   => (clone $commissions)->count(),
            // Sales, booking count and the recent list the web portal
            // dashboard leads with — the app could only show money before.
            'total_sales'        => (float) (clone $bookings)->sum('amount'),
            'booking_count'      => (clone $bookings)->count(),
            'recent_bookings'    => (clone $bookings)->with('package')->latest()->take(8)
                ->get()->map(fn (Booking $b) => $this->bookingInfo($b)),
        ]);
    }

    /** Agent invoices (same rows as the portal's Invoices page). */
    public function invoices(Request $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $invoices = AgentInvoice::where('agent_id', $agent->id)
            ->latest('issued_on')->latest('id')
            ->get()
            ->map(fn (AgentInvoice $i) => [
                'id'            => $i->id,
                'invoice_no'    => $i->invoice_no,
                'customer_name' => $i->customer_name,
                'amount'        => (float) $i->amount,
                'issued_on'     => optional($i->issued_on)->toDateString(),
                'due_on'        => optional($i->due_on)->toDateString(),
                'status'        => $i->status,
            ]);

        return $this->responseWithSuccess('Agent invoices fetched.', [
            'invoices' => $invoices,
            'summary'  => [
                'total' => (float) $invoices->sum('amount'),
                'count' => $invoices->count(),
            ],
        ]);
    }

    /**
     * Wallet ledger on its own, for the portal's Transactions page. The wallet
     * endpoint returns the same rows alongside the balance.
     */
    public function transactions(Request $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $txns = AgentWalletTransaction::where('agent_id', $agent->id)
            ->latest('txn_date')->latest('id')
            ->get()
            ->map(fn (AgentWalletTransaction $t) => [
                'id'            => $t->id,
                'reference'     => $t->reference,
                'type'          => $t->type,
                'amount'        => (float) $t->amount,
                'description'   => $t->description,
                'txn_date'      => optional($t->txn_date)->toDateString(),
                'balance_after' => (float) $t->balance_after,
            ]);

        return $this->responseWithSuccess('Agent transactions fetched.', [
            'transactions' => $txns,
        ]);
    }

    /** Performance totals — the portal's Reports page. */
    public function reports(Request $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $bookings = Booking::where('agent_id', $agent->id);

        return $this->responseWithSuccess('Agent reports fetched.', [
            'total_sales' => (float) (clone $bookings)->sum('amount'),
            'bookings'    => (clone $bookings)->count(),
            'commission'  => (float) AgentCommission::where('agent_id', $agent->id)->sum('amount'),
            'customers'   => (clone $bookings)->whereNotNull('customer_id')
                ->distinct('customer_id')->count('customer_id'),
            // Keyed maps so the app can chart them without a second call.
            // Cast to object so an empty result stays {} instead of flipping to
            // [] and breaking the client's Map decoding.
            'by_status'         => (object) (clone $bookings)->selectRaw('status, count(*) as total')
                ->groupBy('status')->pluck('total', 'status')->toArray(),
            'invoice_by_status' => (object) AgentInvoice::where('agent_id', $agent->id)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')->pluck('total', 'status')->toArray(),
        ]);
    }

    /** Tickets assigned to this agent, plus any raised by their own clients. */
    public function support(Request $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $customerIds = Booking::where('agent_id', $agent->id)
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id');

        $tickets = SupportTicket::where(fn ($q) => $q
                ->where('assigned_to', $agent->id)
                ->orWhere('raised_by', $agent->id)
                ->orWhereIn('customer_id', $customerIds))
            ->latest()
            ->get()
            ->map(fn (SupportTicket $t) => [
                'id'            => $t->id,
                'ticket_no'     => $t->ticket_no,
                'subject'       => $t->subject,
                'customer_name' => $t->customer_name,
                'priority'      => $t->priority,
                'department'    => $t->department,
                'status'        => $t->status,
                'updated_at'    => optional($t->updated_at)->toDateTimeString(),
            ]);

        return $this->responseWithSuccess('Agent support tickets fetched.', [
            'tickets' => $tickets,
        ]);
    }

    public function wallet(Request $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $txns = AgentWalletTransaction::where('agent_id', $agent->id)
            ->orderByDesc('id')
            ->get();

        $settlement = app(\App\Services\Accounting\AgentSettlementService::class);
        $summary    = $settlement->summary($agent->id);

        return $this->responseWithSuccess('Agent wallet.', [
            'balance'      => $summary['balance'],
            // What can be requested right now: balance less anything already
            // requested and not yet paid.
            'available'    => $summary['available'],
            'pending_commission' => $summary['pending'],
            'withdrawn'    => $summary['withdrawn'],
            'currency'     => 'BDT',
            'methods'      => \App\Models\AgentWithdrawal::METHODS,
            'withdrawals'  => \App\Models\AgentWithdrawal::where('agent_id', $agent->id)
                ->orderByDesc('id')->get()
                ->map(fn (\App\Models\AgentWithdrawal $w) => [
                    'id'           => $w->id,
                    'reference'    => $w->reference,
                    'amount'       => (float) $w->amount,
                    'method'       => $w->method,
                    'status'       => $w->status,
                    'requested_on' => optional($w->requested_on)->toDateString(),
                    'processed_on' => optional($w->processed_on)->toDateString(),
                    'note'         => $w->note,
                ]),
            'transactions' => $txns->map(fn (AgentWalletTransaction $t) => [
                'id'            => $t->id,
                'reference'     => $t->reference,
                'type'          => $t->type,
                'amount'        => (float) $t->amount,
                'description'   => $t->description,
                'txn_date'      => optional($t->txn_date)->toDateString(),
                'balance_after' => (float) $t->balance_after,
            ]),
        ]);
    }

    /**
     * Ask to be paid out of the wallet. Same rules as the web portal: only
     * approved commissions are withdrawable, and money already requested
     * cannot be requested again.
     */
    public function requestWithdrawal(\App\Http\Requests\AgentWithdrawal\RequestWithdrawalRequest $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $result = app(\App\Repositories\AgentWithdrawal\AgentWithdrawalInterface::class)
            ->requestForAgent($request, (int) $agent->id);

        if (! $result['status']) {
            return $this->responseWithError($result['message'], [], 422);
        }

        $settlement = app(\App\Services\Accounting\AgentSettlementService::class);

        return $this->responseWithSuccess($result['message'], [
            'available' => $settlement->availableBalance((int) $agent->id),
            'balance'   => $settlement->walletBalance((int) $agent->id),
        ]);
    }

    public function commissions(Request $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $items = AgentCommission::where('agent_id', $agent->id)
            ->latest('earned_on')
            ->get()
            ->map(fn (AgentCommission $c) => [
                'id'            => $c->id,
                'reference'     => $c->reference,
                'booking_ref'   => $c->booking_ref,
                'customer_name' => $c->customer_name,
                'amount'        => (float) $c->amount,
                'rate'          => (float) $c->rate,
                'status'        => $c->status,
                'earned_on'     => optional($c->earned_on)->toDateString(),
            ]);

        $mine = fn () => AgentCommission::where('agent_id', $agent->id);

        return $this->responseWithSuccess('Commissions fetched.', [
            'commissions' => $items,
            // The four figures the portal's Commissions page leads with; the
            // app could only show the rows and had no totals of its own.
            'summary' => [
                'earned'  => (float) $mine()->sum('amount'),
                'pending' => (float) $mine()->where('status', 'pending')->sum('amount'),
                // 'paid' means credited to the wallet — withdrawable, and only
                // out of the agency once a payout is paid.
                'paid'    => (float) $mine()->where('status', 'paid')->sum('amount'),
                'rate'    => round((float) ($mine()->avg('rate') ?? 0), 2),
            ],
        ]);
    }

    /**
     * Bookings the agent made on behalf of their clients.
     */
    public function bookings(Request $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $bookings = Booking::with('package')
            ->where('agent_id', $agent->id)
            ->latest()
            ->get()
            ->map(fn (Booking $b) => $this->bookingInfo($b));

        return $this->responseWithSuccess('Agent bookings fetched.', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Create a booking for a walk-in client (net fare = package price).
     */
    public function storeBooking(\App\Http\Requests\Booking\StoreAgentBookingRequest $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $package = Package::where('status', 'active')->find($request->package_id);
        if (! $package) {
            return $this->responseWithError('Tour not available.', [], 404);
        }

        // Shared with the portal's own booking form, so a sale is the same
        // record whichever surface the agent used.
        $booking = app(CreateAgentBooking::class)($agent, $package, $request->validated());

        return $this->responseWithSuccess('Booking created for client.', [
            'booking' => $this->bookingInfo($booking),
        ], 201);
    }

    /**
     * Sell a hotel stay to a client. Same action the portal form uses, so a
     * sale is the same record whichever surface the agent used: the price is
     * quoted server-side from the room's published rate, the client gets a
     * customer record, and the stay opens as Booked until the desk confirms
     * and takes payment.
     */
    public function storeHotelBooking(\App\Http\Requests\HotelBooking\StoreAgentHotelRequest $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $hotel = Hotel::where('status', 'active')->find($request->hotel_id);
        if (! $hotel) {
            return $this->responseWithError('Hotel not available.', [], 404);
        }

        $room = HotelRoom::where('hotel_id', $hotel->id)->find($request->hotel_room_id);
        if (! $room) {
            return $this->responseWithError('Room not available at this hotel.', [], 404);
        }

        $stay = app(CreateAgentHotelBooking::class)($agent, $hotel, $room, $request->validated());

        return $this->responseWithSuccess('Hotel stay created for client.', [
            'booking' => $this->hotelBookingInfo($stay),
        ], 201);
    }

    /**
     * Sell a transport trip to a client. Same action the portal form uses, so
     * a sale is the same record whichever surface the agent used: the client
     * gets a customer record, the trip is stamped with the agent and opens
     * Pending at fare 0 — the desk prices it, and the commission is worked
     * out once it is actually paid.
     */
    public function storeTransportBooking(\App\Http\Requests\Transport\StoreAgentTransportRequest $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $trip = app(CreateAgentTransportBooking::class)($agent, $request->validated());

        return $this->responseWithSuccess('Transport trip opened for client.', [
            'transport' => $this->transportInfo($trip),
        ], 201);
    }

    /**
     * Sell a flight to a client. Same action the portal form uses, so a sale
     * is the same record whichever surface the agent used: the request opens
     * Pending at fare 0 with a placeholder PNR, the client gets a customer
     * record, and the agent is stamped — once the desk prices it and issues
     * the ticket, the sale earns the agent their commission.
     */
    public function storeFlightBooking(\App\Http\Requests\Flight\StoreAgentFlightRequest $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $flight = app(CreateAgentFlightBooking::class)($agent, $request->validated());

        return $this->responseWithSuccess('Flight request filed for client.', [
            'flight' => $this->flightInfo($flight),
        ], 201);
    }

    /** One booking the agent made, with the detail the list cannot carry: who
     * the client is, what the agency has invoiced, and what the agent earns
     * on it. The portal's list gave no way to open a booking at all.
     */
    public function showBooking(Request $request, $id)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $booking = $this->agentBooking($agent, $id);
        if (! $booking) {
            return $this->responseWithError('Booking not found.', [], 404);
        }

        $invoice    = Invoice::where('booking_id', $booking->id)->first();
        $commission = AgentCommission::where('agent_id', $agent->id)
            ->where('booking_id', $booking->id)
            ->first();

        return $this->responseWithSuccess('Booking detail fetched.', [
            'booking' => $this->bookingInfo($booking) + [
                'customer_phone' => $booking->customer_phone,
                'customer_email' => $booking->customer_email,
                'notes'          => $booking->notes,
                // What the agent may still do from here, so the client does
                // not have to guess which buttons will 409.
                'can_amend'      => $booking->status === 'pending',
                'can_cancel'     => $booking->status === 'pending',
            ],
            'invoice' => $invoice ? [
                'invoice_no'  => $invoice->invoice_no,
                'amount'      => (float) $invoice->amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'due_amount'  => round(max(0, $invoice->dueAmount()), 2),
                'status'      => $invoice->status,
                'issue_date'  => optional($invoice->issue_date)->toDateString(),
                'due_date'    => optional($invoice->due_date)->toDateString(),
            ] : null,
            'commission' => $commission ? [
                'reference' => $commission->reference,
                'amount'    => (float) $commission->amount,
                'rate'      => (float) $commission->rate,
                'status'    => $commission->status,
                'earned_on' => optional($commission->earned_on)->toDateString(),
            ] : null,
        ]);
    }

    /**
     * Amend a booking the desk has not started billing yet: the party size or
     * the travel date. The amount is re-quoted from the package rather than
     * accepted from the client, so an agent cannot set their own price.
     *
     * Only while pending — once the office has confirmed or taken money the
     * change is theirs to make, not the agent's.
     */
    public function updateBooking(\App\Http\Requests\Booking\AmendAgentBookingRequest $request, $id)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $booking = $this->agentBooking($agent, $id);
        if (! $booking) {
            return $this->responseWithError('Booking not found.', [], 404);
        }
        if ($booking->status !== 'pending') {
            return $this->responseWithError(
                "A {$booking->status} booking can only be changed by the office.", [], 409);
        }

        $package = Package::find($booking->package_id);

        $booking->update([
            'travel_date' => $request->travel_date,
            'travelers'   => $request->travelers,
            'amount'      => $package
                ? (float) $package->price * (int) $request->travelers
                : $booking->amount,
        ]);

        // BookingObserver keeps the invoice in step; an unpaid invoice follows
        // the new amount, so nothing has to be re-raised here.
        return $this->responseWithSuccess('Booking updated.', [
            'booking' => $this->bookingInfo($booking->fresh()),
        ]);
    }

    /**
     * Cancel a booking the agent raised, while it is still pending.
     *
     * A paid booking is deliberately not cancellable here: refunding it moves
     * money out of the agency and reverses a commission, which is the office's
     * call in Bookings > Manage, not an external agent's.
     */
    public function cancelBooking(Request $request, $id)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $booking = $this->agentBooking($agent, $id);
        if (! $booking) {
            return $this->responseWithError('Booking not found.', [], 404);
        }
        if ($booking->status === 'cancelled') {
            return $this->responseWithError('Booking already cancelled.', [], 409);
        }
        if ($booking->status !== 'pending') {
            return $this->responseWithError(
                "A {$booking->status} booking has to be cancelled by the office.", [], 409);
        }

        // Nothing has been received on a pending booking, so there is no refund
        // to post; BookingObserver withdraws the unpaid invoice on save.
        $booking->update(['status' => 'cancelled']);

        if ($booking->customer_id) {
            \App\Models\Notification::notify(
                \App\Models\Customer::find($booking->customer_id),
                'Booking cancelled',
                "Booking #{$booking->id} has been cancelled by your agent.",
                'booking'
            );
        }

        return $this->responseWithSuccess('Booking cancelled.', [
            'booking' => $this->bookingInfo($booking->fresh()),
        ]);
    }

    /** A booking, only if this agent raised it. */
    private function agentBooking(User $agent, $id): ?Booking
    {
        return Booking::with('package')
            ->where('agent_id', $agent->id)
            ->find($id);
    }

    /** The stay as the app renders it, with the room and hotel names. */
    private function hotelBookingInfo(\App\Models\HotelBooking $b): array
    {
        return [
            'id'         => $b->id,
            'booking_no' => $b->booking_no,
            'hotel'      => $b->hotel?->name,
            'room_type'  => $b->hotelRoom?->room_type,
            'check_in'   => optional($b->check_in)->toDateString(),
            'check_out'  => optional($b->check_out)->toDateString(),
            'nights'     => (int) $b->nights,
            'amount'     => (float) $b->amount,
            'status'     => $b->status,
        ];
    }

    /** The flight request as the app renders it. */
    private function flightInfo(\App\Models\FlightBooking $f): array
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

    /** The trip as the app renders it. */
    private function transportInfo(\App\Models\TransportBooking $t): array
    {
        return [
            'id'            => $t->id,
            'booking_no'    => $t->booking_no,
            'type'          => $t->type,
            'direction'     => $t->direction,
            'customer_name' => $t->customer_name,
            'route'         => $t->route,
            'travel_date'   => optional($t->travel_date)->toDateString(),
            'vehicle'       => $t->vehicle,
            'fare'          => (float) $t->fare,
            'status'        => $t->status,
        ];
    }

    /**
     * The agent's own clients — the customer records behind the bookings they
     * made, the same set the portal's Customers page lists.
     *
     * This used to group the bookings by name and phone, so a client whose
     * name was typed slightly differently split into two rows and neither
     * carried an email, a tier or a status. Every agent booking attaches a
     * customer now, so the real records can be read instead.
     */
    public function customers(Request $request)
    {
        $agent = $this->agent($request);
        if (! $agent) {
            return $this->responseWithError('Agents only.', [], 403);
        }

        $totals = Booking::where('agent_id', $agent->id)
            ->whereNotNull('customer_id')
            ->selectRaw('customer_id, COUNT(*) as bookings, SUM(amount) as total')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        $clients = Customer::whereHas('bookings', fn ($q) => $q->where('agent_id', $agent->id))
            ->latest()
            ->get()
            ->map(fn (Customer $c) => [
                'id'       => $c->id,
                'name'     => $c->name,
                'email'    => $c->email,
                'phone'    => $c->phone,
                'tier'     => $c->tier,
                'status'   => $c->status,
                'bookings' => (int) ($totals[$c->id]->bookings ?? 0),
                'total'    => (float) ($totals[$c->id]->total ?? 0),
            ]);

        return $this->responseWithSuccess('Clients fetched.', ['clients' => $clients]);
    }

    private function walletBalance($agentId): float
    {
        return app(\App\Services\Accounting\AgentSettlementService::class)
            ->walletBalance((int) $agentId);
    }

    /**
     * The authenticated user, only if they are an Agent (role name "Agent").
     */
    private function agent(Request $request): ?User
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return null;
        }
        return strtolower((string) optional($user->role)->name) === 'agent' ? $user : null;
    }
}
