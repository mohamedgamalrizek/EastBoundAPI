<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\FlightBooking;
use App\Repositories\Flight\FlightInterface;
use App\Http\Requests\Flight\StoreFlightRequest;
use App\Http\Requests\Flight\UpdateFlightRequest;
use App\Http\Controllers\Backend\Concerns\BuildsListAnalytics;

class FlightController extends BaseCrudController
{
    use BuildsListAnalytics;

    protected string $viewPath      = 'backend.flight';
    protected string $redirectRoute = 'flight.index';

    public function __construct(FlightInterface $repo)
    {
        $this->repo = $repo;
    }

    protected function listAnalytics(Request $request): ?array
    {
        return [
            'stats' => [
                'Total Bookings' => FlightBooking::count(),
                'Total Fare'     => currency_symbol() . number_format((float) FlightBooking::sum('fare')),
                'Confirmed'      => FlightBooking::where('status', 'Confirmed')->count(),
                'Pending'        => FlightBooking::where('status', 'Pending')->count(),
            ],
            'donut' => $this->laGroup(FlightBooking::class, 'status'),
            'trend' => $this->laMonthly(FlightBooking::class, 'Fare', 'SUM(fare)', 'area', 'created_at', 'float'),
        ];
    }

    public function store(StoreFlightRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateFlightRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /** Single flight-booking detail page. */
    public function booking($id)
    {
        return view('backend.flight.booking', ['booking' => $this->repo->find($id)]);
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function reissue()
    {
        return view('backend.flight.reissue', $this->repo->reissue());
    }

    /** Reissue / cancel / refund all move a ticket through the same gate. */
    public function settle(Request $request, $id)
    {
        $result = $this->repo->settle($request, $id);

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }

    public function cancellation()
    {
        return view('backend.flight.cancellation', $this->repo->cancellation());
    }

    public function refund()
    {
        return view('backend.flight.refund', $this->repo->refund());
    }

    public function reports()
    {
        return view('backend.flight.reports', $this->repo->reports());
    }
}
