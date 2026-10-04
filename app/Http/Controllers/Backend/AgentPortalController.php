<?php

namespace App\Http\Controllers\Backend;

use App\Actions\RaiseSupportTicket;
use App\Http\Controllers\Controller;
use App\Repositories\AgentPortal\AgentPortalInterface;
use App\Repositories\AgentWithdrawal\AgentWithdrawalInterface;
use App\Http\Requests\AgentWithdrawal\RequestWithdrawalRequest;
use App\Http\Requests\Booking\AmendAgentBookingRequest;
use App\Http\Requests\Booking\StoreAgentBookingRequest;
use App\Http\Requests\Flight\StoreAgentFlightRequest;
use App\Http\Requests\HotelBooking\StoreAgentHotelRequest;
use App\Http\Requests\Support\RaiseTicketRequest;
use App\Http\Requests\Transport\StoreAgentTransportRequest;

class AgentPortalController extends Controller
{
    protected $repo;

    public function __construct(AgentPortalInterface $repo)
    {
        $this->repo = $repo;
    }

    public function dashboard()
    {
        return view('backend.portal.agent.dashboard', $this->repo->dashboard());
    }

    public function bookings()
    {
        return view('backend.portal.agent.bookings', $this->repo->bookings());
    }

    public function bookingCreate()
    {
        return view('backend.portal.agent.booking-create', $this->repo->bookingFormData());
    }

    public function bookingStore(StoreAgentBookingRequest $request)
    {
        $result = $this->repo->storeBooking($request);

        if (! $result['status']) {
            return back()->withInput()->with('danger', $result['message']);
        }

        return redirect()
            ->route('agent.booking.show', $result['data']['booking']->id)
            ->with('success', $result['message']);
    }

    public function booking($id)
    {
        return view('backend.portal.agent.booking-details', $this->repo->booking($id));
    }

    public function bookingUpdate(AmendAgentBookingRequest $request, $id)
    {
        $result = $this->repo->updateBooking($request, $id);

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }

    public function bookingCancel($id)
    {
        $result = $this->repo->cancelBooking($id);

        return redirect()->route('agent.bookings')
            ->with($result['status'] ? 'success' : 'danger', $result['message']);
    }

    public function hotelCreate()
    {
        return view('backend.portal.agent.hotel-create', $this->repo->hotelFormData());
    }

    public function hotelStore(StoreAgentHotelRequest $request)
    {
        $result = $this->repo->storeHotelBooking($request);

        if (! $result['status']) {
            return back()->withInput()->with('danger', $result['message']);
        }

        return redirect()
            ->route('agent.bookings')
            ->with('success', $result['message']);
    }

    public function flightCreate()
    {
        return view('backend.portal.agent.flight-create', $this->repo->flightFormData());
    }

    public function flightStore(StoreAgentFlightRequest $request)
    {
        $result = $this->repo->storeFlightBooking($request);

        if (! $result['status']) {
            return back()->withInput()->with('danger', $result['message']);
        }

        return redirect()
            ->route('agent.bookings')
            ->with('success', $result['message']);
    }

    public function transportCreate()
    {
        return view('backend.portal.agent.transport-create', $this->repo->transportFormData());
    }

    public function transportStore(StoreAgentTransportRequest $request)
    {
        $result = $this->repo->storeTransportBooking($request);

        if (! $result['status']) {
            return back()->withInput()->with('danger', $result['message']);
        }

        return redirect()
            ->route('agent.bookings')
            ->with('success', $result['message']);
    }

    public function customers()
    {
        return view('backend.portal.agent.customers', $this->repo->customers());
    }

    /**
     * A visa document for one of the agent's own customers — ownedVisaDocument()
     * scopes it to customers the agent has actually booked for, so this can't
     * be used to browse another agent's clients' paperwork.
     */
    public function downloadVisaDocument($id)
    {
        $document = $this->repo->ownedVisaDocument($id);

        if (! $document) {
            abort(404);
        }

        $disk = \App\Repositories\Visa\VisaRepository::DOCUMENT_DISK;

        abort_unless(\Illuminate\Support\Facades\Storage::disk($disk)->exists($document->file_path), 404);

        return \Illuminate\Support\Facades\Storage::disk($disk)->download($document->file_path, $document->file_name);
    }

    public function commissions()
    {
        return view('backend.portal.agent.commissions', $this->repo->commissions());
    }

    public function wallet()
    {
        return view('backend.portal.agent.wallet', $this->repo->wallet());
    }

    public function transactions()
    {
        return view('backend.portal.agent.transactions', $this->repo->transactions());
    }

    public function invoices()
    {
        return view('backend.portal.agent.invoices', $this->repo->invoices());
    }

    public function reports()
    {
        return view('backend.portal.agent.reports', $this->repo->reports());
    }

    public function support()
    {
        return view('backend.portal.agent.support', $this->repo->support());
    }

    /**
     * The agent opens a ticket of their own. Routing (department, assignee)
     * stays with the desk in the admin Support module; the agent only says
     * what it is about and how urgent it is.
     */
    public function raiseTicket(RaiseTicketRequest $request)
    {
        $ticket = app(RaiseSupportTicket::class)(
            auth()->user(),
            $request->subject,
            $request->input('priority')
        );

        return back()->with('success', "Ticket {$ticket->ticket_no} has been received.");
    }

    /**
     * The agent asks to be paid out of their wallet. The office reviews it in
     * Agent Finance > Payouts; nothing moves until it is marked paid there.
     */
    public function requestWithdrawal(RequestWithdrawalRequest $request, AgentWithdrawalInterface $withdrawals)
    {
        $result = $withdrawals->requestForAgent($request, (int) auth()->id());

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }
}
