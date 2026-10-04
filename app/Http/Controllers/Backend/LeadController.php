<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\User;
use App\Repositories\Flight\FlightInterface;
use App\Repositories\Flight\FlightRepository;
use App\Repositories\HotelBooking\HotelBookingInterface;
use App\Repositories\HotelBooking\HotelBookingRepository;
use App\Repositories\Lead\LeadInterface;
use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadRequest;
use App\Services\Accounting\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    protected $repo;

    protected FlightInterface $flightRepo;

    protected HotelBookingInterface $hotelBookingRepo;

    public function __construct(LeadInterface $repo, FlightInterface $flightRepo, HotelBookingInterface $hotelBookingRepo)
    {
        $this->repo = $repo;
        $this->flightRepo = $flightRepo;
        $this->hotelBookingRepo = $hotelBookingRepo;
    }

    public function index()
    {
        $leads = $this->repo->all();

        return view('backend.crm.leads', compact('leads'));
    }

    public function show($id)
    {
        $lead = $this->repo->get($id);

        abort_if(! $lead, 404);

        $activities = CrmActivity::with('user')
            ->where('lead_id', $id)
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->get();

        return view('backend.crm.lead-details', compact('lead', 'activities'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('backend.crm.lead-create', compact('users'));
    }

    public function store(StoreLeadRequest $request)
    {
        $result = $this->repo->store($request);

        if ($result['status']) {
            return redirect()->route('crm.leads')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function edit($id)
    {
        $lead = $this->repo->get($id);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('backend.crm.lead-edit', compact('lead', 'users'));
    }

    public function update(UpdateLeadRequest $request, $id)
    {
        $result = $this->repo->update($request, $id);

        if ($result['status']) {
            return redirect()->route('crm.leads')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }

    /* ---------------------------------------------------------------------
     | Lead → Flight booking conversion
     * ------------------------------------------------------------------- */

    /**
     * Open the flight booking form pre-filled from the lead (passenger name,
     * route and date parsed out of the website enquiry's notes). The desk
     * still adds PNR, airline and fare — a lead never carries those.
     */
    public function convertFlight($id)
    {
        $lead = $this->repo->get($id);

        abort_if(! $lead, 404);

        $from = $this->leadField($lead->notes, 'From');
        $to   = $this->leadField($lead->notes, 'To');
        $date = $this->leadDate($lead->notes);

        $item = new FlightBooking();
        $item->passenger_name = $lead->name;
        $item->route          = $from && $to ? "{$from} -> {$to}" : ($from ?: $to);
        $item->flight_date    = $date;
        $item->fare           = (float) $lead->value;
        $item->status         = 'Pending';

        return view('backend.crm.lead-convert-flight', array_merge(
            ['item' => $item, 'lead' => $lead],
            $this->flightRepo->formData()
        ));
    }

    /**
     * Create the FlightBooking from the convert form, then close the lead out:
     * mark it Won, append a note, and log the conversion on its timeline.
     */
    public function storeFlightBooking(Request $request, $id)
    {
        $lead = $this->repo->get($id);

        abort_if(! $lead, 404);

        $data = $request->validate([
            'customer_id'    => ['nullable', 'exists:customers,id'],
            'booking_id'     => ['nullable', 'exists:bookings,id'],
            'pnr'            => ['required', 'string', 'max:50', 'unique:flight_bookings,pnr'],
            'passenger_name' => ['required', 'string', 'max:100'],
            'airline'        => ['required', 'string', 'max:100'],
            'route'          => ['required', 'string', 'max:100'],
            'flight_date'    => ['required', 'date'],
            'ticket_no'      => ['nullable', 'string', 'max:50'],
            'fare'           => ['required', 'numeric', 'min:0'],
            'status'         => ['required', Rule::in(FlightRepository::STATUSES)],
        ]);

        $flight = FlightBooking::create([
            'customer_id'    => $data['customer_id'] ?? null,
            'booking_id'     => $data['booking_id'] ?? null,
            'pnr'            => $data['pnr'],
            'passenger_name' => $data['passenger_name'],
            'airline'        => $data['airline'],
            'route'          => $data['route'],
            'flight_date'    => $data['flight_date'],
            'ticket_no'      => $data['ticket_no'] ?? null,
            'fare'           => $data['fare'],
            'status'         => $data['status'],
        ]);

        activity()
            ->performedOn($flight)
            ->causedBy(auth()->user())
            ->event('created')
            ->log("created FlightBooking #{$flight->id} from lead #{$lead->id}");

        $this->markLeadConverted($lead, 'Converted to flight booking',
            "Converted to flight booking #{$flight->id} (PNR {$flight->pnr}).");

        return redirect()->route('flight.booking', $flight->id)
            ->with('success', "Flight booking {$flight->pnr} created from this lead.");
    }

    /**
     * Open the hotel booking form pre-filled from the lead (guest name, check
     * in/out dates and nights parsed out of the enquiry notes). The desk still
     * picks the hotel, room and booking number.
     */
    public function convertHotel($id)
    {
        $lead = $this->repo->get($id);

        abort_if(! $lead, 404);

        $checkIn  = $this->leadDate($lead->notes, 'Checkin');
        $checkOut = $this->leadDate($lead->notes, 'Checkout');

        $item = new HotelBooking();
        $item->guest_name = $lead->name;
        $item->check_in   = $checkIn;
        $item->check_out  = $checkOut;
        $item->nights     = $checkIn && $checkOut ? max(0, $checkIn->diffInDays($checkOut)) : 0;
        $item->amount     = (float) $lead->value;
        $item->status     = 'Booked';

        return view('backend.crm.lead-convert-hotel', array_merge(
            ['item' => $item, 'lead' => $lead],
            $this->hotelBookingRepo->formData()
        ));
    }

    /**
     * Create the HotelBooking from the convert form, then close the lead out
     * the same way the flight conversion does: Won + timeline entry.
     */
    public function storeHotelBooking(Request $request, $id)
    {
        $lead = $this->repo->get($id);

        abort_if(! $lead, 404);

        $data = $request->validate([
            'booking_no'     => ['required', 'string', 'max:50', 'unique:hotel_bookings,booking_no'],
            'hotel_id'       => ['required', 'exists:hotels,id'],
            'customer_id'    => ['nullable', 'exists:customers,id'],
            'hotel_room_id'  => ['required', 'exists:hotel_rooms,id'],
            'guest_name'     => ['required', 'string', 'max:100'],
            'check_in'       => ['required', 'date'],
            'check_out'      => ['required', 'date', 'after_or_equal:check_in'],
            'nights'         => ['required', 'integer', 'min:0'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'status'         => ['required', Rule::in(HotelBookingRepository::STATUSES)],
            'payment_method' => ['nullable', Rule::in(BillingService::PAYMENT_METHODS)],
        ]);

        $booking = HotelBooking::create([
            'booking_no'     => $data['booking_no'],
            'hotel_id'       => $data['hotel_id'],
            'customer_id'    => $data['customer_id'] ?? null,
            'hotel_room_id'  => $data['hotel_room_id'],
            'guest_name'     => $data['guest_name'],
            'check_in'       => $data['check_in'],
            'check_out'      => $data['check_out'],
            'nights'         => $data['nights'],
            'amount'         => $data['amount'],
            'status'         => $data['status'],
            'payment_method' => $data['payment_method'] ?? null,
        ]);

        activity()
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->event('created')
            ->log("created HotelBooking #{$booking->id} from lead #{$lead->id}");

        $this->markLeadConverted($lead, 'Converted to hotel booking',
            "Converted to hotel booking #{$booking->id} ({$booking->booking_no}).");

        return redirect()->route('hotel.booking.index')
            ->with('success', "Hotel booking {$booking->booking_no} created from this lead.");
    }

    /**
     * Close a lead once its enquiry has become a real booking: Won stage, a
     * note appended, and an entry on its activity timeline.
     */
    private function markLeadConverted($lead, string $subject, string $body): void
    {
        $lead->stage = 'Won';
        $lead->notes = trim(($lead->notes ?: '') . "\n\n" . $body);
        $lead->save();

        CrmActivity::create([
            'lead_id'       => $lead->id,
            'user_id'       => auth()->id(),
            'customer_name' => $lead->name,
            'type'          => 'Booking',
            'subject'       => $subject,
            'body'          => $body,
            'activity_date' => now(),
            'channel'       => 'System',
        ]);
    }

    /**
     * Pull a "Label: value" line out of the lead notes — the website enquiry
     * form stores its fields as one line each ("From: Dhaka", "To: Dubai").
     */
    private function leadField(?string $notes, string $label): string
    {
        if (! $notes || ! preg_match('/^' . preg_quote($label, '/') . ':\s*(.+)$/mi', $notes, $m)) {
            return '';
        }

        return trim($m[1]);
    }

    private function leadDate(?string $notes, string $label = 'Departure Date'): ?Carbon
    {
        $value = $this->leadField($notes, $label);

        return $value ? Carbon::parse($value) : null;
    }
}
