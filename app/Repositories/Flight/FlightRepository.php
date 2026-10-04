<?php

namespace App\Repositories\Flight;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\FlightBooking;
use App\Models\FlightRoute;
use App\Repositories\BaseRepository;
use App\Repositories\Flight\FlightInterface;

class FlightRepository extends BaseRepository implements FlightInterface
{
    /** Allowed status set — mirror the flight_bookings migration. */
    public const STATUSES = ['Confirmed', 'Pending', 'Cancelled', 'Refunded', 'Reissued'];

    protected array $with = ['customer', 'booking'];

    public function __construct(FlightBooking $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'customer_id'    => $request->customer_id ?: null,
            'booking_id'     => $request->booking_id ?: null,
            'pnr'            => $request->pnr,
            'passenger_name' => $request->passenger_name,
            'airline'        => $request->airline,
            'route'          => $request->route,
            'flight_date'    => $request->flight_date ?: null,
            'ticket_no'      => $request->ticket_no ?: null,
            'fare'           => $request->fare,
            'status'         => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('pnr', 'like', "%{$search}%")
                    ->orWhere('passenger_name', 'like', "%{$search}%")
                    ->orWhere('airline', 'like', "%{$search}%")
                    ->orWhere('route', 'like', "%{$search}%")
                    ->orWhere('ticket_no', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['airline'])) {
            $query->where('airline', $filters['airline']);
        }
    }

    public function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'bookings'  => Booking::latest()->get(['id', 'customer_id', 'customer_name']),
            'statuses'  => self::STATUSES,
            // City/code options for the From/To route picker — the same list
            // the website /book/flight form uses, so the desk picks a route
            // exactly the way a customer does. The composed route string is
            // still stored on flight_bookings.route.
            'cities'    => FlightRoute::active()
                ->get(['origin', 'origin_code', 'destination', 'destination_code'])
                ->flatMap(fn (FlightRoute $r) => [
                    (object) ['city' => $r->origin, 'code' => $r->origin_code],
                    (object) ['city' => $r->destination, 'code' => $r->destination_code],
                ])
                ->unique('city')
                ->sortBy('city')
                ->values(),
        ];
    }

    /* ---------------------------------------------------------------------
     | Management pages
     * ------------------------------------------------------------------- */

    /**
     * Each of these pages lists what has already been settled plus the tickets
     * eligible for that action — the lists alone left no way to actually
     * reissue, cancel or refund anything.
     */
    public function reissue()
    {
        return [
            'bookings'  => FlightBooking::where('status', 'Reissued')->latest()->get(),
            'eligible'  => FlightBooking::where('status', 'Confirmed')->latest()->get(),
            'action'    => 'Reissued',
        ];
    }

    public function cancellation()
    {
        return [
            'bookings'  => FlightBooking::where('status', 'Cancelled')->latest()->get(),
            'eligible'  => FlightBooking::whereIn('status', ['Confirmed', 'Pending', 'Reissued'])->latest()->get(),
            'action'    => 'Cancelled',
        ];
    }

    public function refund()
    {
        $refunded = FlightBooking::where('status', 'Refunded')->latest()->get();

        return [
            'bookings'  => $refunded,
            // Only a cancelled ticket can be refunded; the airline has to
            // release the money before anything is paid back.
            'eligible'  => FlightBooking::where('status', 'Cancelled')->latest()->get(),
            'action'    => 'Refunded',
            'refunded'  => (float) $refunded->sum('refund_amount'),
            'retained'  => (float) $refunded->sum('penalty'),
        ];
    }

    /**
     * Move a ticket to its next state, recording the money involved.
     *
     * Refunds carry a penalty the airline keeps, so the amount returned is
     * stored separately from the original fare rather than inferred from it.
     */
    public function settle($request, $id)
    {
        try {
            $ticket = $this->find($id);

            if (! $ticket) {
                return $this->responseWithError(___('alert.not_found'));
            }

            $status = (string) $request->input('status');

            if (! $ticket->canMoveTo($status)) {
                return $this->responseWithError(
                    "A {$ticket->status} ticket cannot be marked {$status}."
                );
            }

            $ticket->status            = $status;
            $ticket->status_note       = $request->input('status_note');
            $ticket->status_changed_at = now();

            if ($status === 'Refunded') {
                $refund = (float) $request->input('refund_amount', 0);
                $ticket->refund_amount = $refund;
                // Whatever is not returned stayed with the airline/agency.
                $ticket->penalty = max(0, (float) $ticket->fare - $refund);

                // The money leaving goes through the refund flow, so the
                // ledger sees it (Dr Refunds, Cr Cash/Bank). Attached to the
                // linked booking's invoice when there is one, standalone
                // otherwise. canMoveTo() blocks a second pass, so this cannot
                // refund the same ticket twice.
                app(\App\Services\Accounting\BillingService::class)->refundStandalone(
                    $refund,
                    (string) $request->input('refund_method', 'Cash'),
                    $ticket->customer_id,
                    $ticket->booking_id ? \App\Models\Invoice::where('booking_id', $ticket->booking_id)->first() : null,
                    'Flight ticket ' . $ticket->pnr . ' refunded'
                );
            }

            if ($status === 'Reissued' && $request->filled('flight_date')) {
                $ticket->flight_date = $request->input('flight_date');
            }

            $ticket->save();

            $this->logActivity('updated', $ticket);
            $this->tellPassenger($ticket);

            return $this->responseWithSuccess("Ticket {$ticket->pnr} marked {$status}.");
        } catch (\Throwable $th) {
            return $this->responseWithError('Unable to update the ticket.');
        }
    }

    private function tellPassenger(FlightBooking $ticket): void
    {
        if (! $ticket->customer) {
            return;
        }

        $body = match ($ticket->status) {
            'Reissued'  => "Ticket {$ticket->pnr} has been reissued for "
                . $ticket->flight_date?->format('d M Y') . '.',
            'Cancelled' => "Ticket {$ticket->pnr} has been cancelled.",
            'Refunded'  => currency_symbol() . number_format((float) $ticket->refund_amount)
                . " has been refunded against ticket {$ticket->pnr}.",
            default     => "Ticket {$ticket->pnr} is now {$ticket->status}.",
        };

        \App\Models\Notification::notify(
            $ticket->customer,
            "Flight ticket {$ticket->status}",
            $body,
            'flight'
        );
    }

    public function reports()
    {
        return [
            'byStatus'   => FlightBooking::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'byAirline'  => FlightBooking::selectRaw('airline, count(*) as total')->groupBy('airline')->pluck('total', 'airline'),
            'totalFare'  => FlightBooking::sum('fare'),
            'totalCount' => FlightBooking::count(),
            'ticketed'   => FlightBooking::whereNotNull('ticket_no')->count(),
        ];
    }
}
