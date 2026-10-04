<?php

namespace App\Http\Controllers\Api;

use App\Actions\RaiseSupportTicket;
use App\Models\Customer;
use App\Models\SupportTicket;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Support tickets — list own tickets + raise a new one.
 *
 * Listing is the customer's own queue; an agent reads theirs through
 * /agent/support, which also carries their clients' tickets.
 */
class SupportController extends Controller
{
    use ApiReturnFormatTrait, ResolvesCustomer;

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        $tickets = SupportTicket::where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn (SupportTicket $t) => $this->info($t));

        return $this->responseWithSuccess('Tickets fetched.', ['tickets' => $tickets]);
    }

    /**
     * Raise a ticket. Open to customers and to agents/staff users: an agent
     * has their own business to ask about (a commission, a payout, a client's
     * booking) and could previously only read the queue, never write to it.
     */
    public function store(Request $request)
    {
        $customer = $this->customer($request);
        $user     = $customer ? null : ($request->user() instanceof User ? $request->user() : null);

        if (! $customer && ! $user) {
            return $this->responseWithError('Forbidden.', [], 403);
        }

        // Same limits as the portal's RaiseTicketRequest.
        $validator = Validator::make($request->all(), [
            'subject'  => ['required', 'string', 'max:150'],
            'priority' => ['nullable', 'in:Low,Medium,High'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        // Shared with the web portal's own form, so a ticket looks the same
        // whichever surface raised it.
        $ticket = app(RaiseSupportTicket::class)(
            $customer ?: $user,
            $request->subject,
            $request->input('priority')
        );

        return $this->responseWithSuccess('Ticket created.', [
            'ticket' => $this->info($ticket),
        ], 201);
    }

    private function info(SupportTicket $t): array
    {
        return [
            'id'         => $t->id,
            'ticket_no'  => $t->ticket_no,
            'subject'    => $t->subject,
            'priority'   => $t->priority,
            'department' => $t->department,
            'status'     => $t->status,
            'created_at' => optional($t->created_at)->toDateTimeString(),
        ];
    }
}
