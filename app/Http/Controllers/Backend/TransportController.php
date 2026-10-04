<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\TransportBooking;
use App\Repositories\Transport\TransportInterface;
use App\Http\Requests\Transport\StoreTransportRequest;
use App\Http\Requests\Transport\UpdateTransportRequest;
use App\Http\Controllers\Backend\Concerns\BuildsListAnalytics;

class TransportController extends BaseCrudController
{
    use BuildsListAnalytics;

    protected string $viewPath      = 'backend.transport';
    protected string $redirectRoute = 'transport.index';

    public function __construct(TransportInterface $repo)
    {
        $this->repo = $repo;
    }

    protected function listAnalytics(Request $request): ?array
    {
        return [
            'stats' => [
                'Total Bookings' => TransportBooking::count(),
                'Total Fare'     => currency_symbol() . number_format((float) TransportBooking::sum('fare')),
                'Confirmed'      => TransportBooking::where('status', 'Confirmed')->count(),
                'Pending'        => TransportBooking::where('status', 'Pending')->count(),
            ],
            'donut' => $this->laGroup(TransportBooking::class, 'status'),
            'trend' => $this->laMonthly(TransportBooking::class, 'Fare', 'SUM(fare)', 'area', 'created_at', 'float'),
        ];
    }

    public function store(StoreTransportRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateTransportRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    /** Read-only details for a single transport booking. */
    public function show($id)
    {
        return view('backend.transport.show', [
            'booking' => $this->repo->find($id),
        ]);
    }

    public function bus()
    {
        return view('backend.transport.bus', $this->repo->bus());
    }

    public function train()
    {
        return view('backend.transport.train', $this->repo->train());
    }

    public function launch()
    {
        return view('backend.transport.launch', $this->repo->launch());
    }

    public function car()
    {
        return view('backend.transport.car', $this->repo->car());
    }

    public function airport()
    {
        return view('backend.transport.airport', $this->repo->airport());
    }

    public function reports()
    {
        return view('backend.transport.reports', $this->repo->reports());
    }
}
