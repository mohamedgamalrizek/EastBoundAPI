<?php

namespace App\Http\Controllers\Backend;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Booking\BookingInterface;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;

class BookingController extends Controller
{
    protected $repo;

    public function __construct(BookingInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $bookings  = $this->repo->all();
        $analytics = $this->analytics();

        return view('backend.booking.index', compact('bookings', 'analytics'));
    }

    /**
     * Aggregates for the list-page analytics block (KPI tiles + charts).
     * Computed over ALL bookings, independent of pagination/filtering.
     */
    private function analytics(): array
    {
        $byStatus = Booking::selectRaw('status, COUNT(*) c, SUM(amount) total')
            ->groupBy('status')->get();

        // Last 6 months booking count + revenue trend.
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));
        $trendRows = Booking::selectRaw("DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c, SUM(amount) total")
            ->where('created_at', '>=', $months->first())
            ->groupBy('ym')->pluck('total', 'ym');
        $trendCount = Booking::selectRaw("DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c")
            ->where('created_at', '>=', $months->first())
            ->groupBy('ym')->pluck('c', 'ym');

        return [
            'stats' => [
                'Total Bookings' => $byStatus->sum('c'),
                'Total Revenue'  => currency_symbol() . number_format((float) $byStatus->sum('total')),
                'Confirmed'      => (int) $byStatus->firstWhere('status', 'confirmed')?->c,
                'Pending'        => (int) $byStatus->firstWhere('status', 'pending')?->c,
            ],
            'donut' => [
                'labels' => $byStatus->pluck('status')->map(fn ($s) => ucfirst($s))->all(),
                'series' => $byStatus->pluck('c')->map(fn ($c) => (int) $c)->all(),
            ],
            'trend' => [
                'type'   => 'area',
                'labels' => $months->map(fn ($m) => $m->format('M'))->all(),
                'series' => [
                    ['name' => 'Revenue',  'data' => $months->map(fn ($m) => (float) ($trendRows[$m->format('Y-m')] ?? 0))->all()],
                    ['name' => 'Bookings', 'data' => $months->map(fn ($m) => (int) ($trendCount[$m->format('Y-m')] ?? 0))->all()],
                ],
            ],
        ];
    }

    public function create()
    {
        $packages  = Package::where('status', 'active')->orderBy('title')->get();
        $customers = $this->customerOptions();
        $agents    = $this->agentOptions();

        return view('backend.booking.create', compact('packages', 'customers', 'agents'));
    }

    public function store(StoreBookingRequest $request)
    {
        $result = $this->repo->store($request);

        if ($result['status']) {
            return redirect()->route('booking.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function edit($id)
    {
        $booking   = $this->repo->get($id);
        $packages  = Package::where('status', 'active')->orderBy('title')->get();
        $customers = $this->customerOptions($booking?->customer_id);
        $agents    = $this->agentOptions();

        return view('backend.booking.edit', compact('booking', 'packages', 'customers', 'agents'));
    }

    public function update(UpdateBookingRequest $request, $id)
    {
        $result = $this->repo->update($request, $id);

        if ($result['status']) {
            return redirect()->route('booking.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    /**
     * Customers for the booking form. Active only, plus the one already on the
     * booking (so editing an old booking whose customer went inactive still
     * shows the right name instead of silently blanking the field).
     */
    private function customerOptions($keepId = null)
    {
        return Customer::where('status', 'active')
            ->when($keepId, fn ($q) => $q->orWhere('id', $keepId))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);
    }

    /** Users carrying the Agent role — the B2B agents who can own a booking. */
    private function agentOptions()
    {
        return User::where('role_id', Role::where('name', 'Agent')->value('id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }
}
