<?php

namespace App\Http\Controllers\Backend;

use App\Models\HajjPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Repositories\Hajj\HajjInterface;
use App\Http\Requests\Hajj\StoreHajjRequest;
use App\Http\Requests\Hajj\UpdateHajjRequest;

class HajjController extends BaseCrudController
{
    protected string $viewPath      = 'backend.hajj';
    protected string $redirectRoute = 'hajj.index';

    public function __construct(HajjInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        return view("{$this->viewPath}.index", [
            'items'     => $this->repo->all($request->query()),
            'analytics' => $this->analytics(),
        ]);
    }

    /** KPI tiles + charts for the Hajj packages list (over all packages). */
    private function analytics(): array
    {
        $byStatus = HajjPackage::selectRaw('status, COUNT(*) c, SUM(seats) seats')
            ->groupBy('status')->get();

        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));
        $added  = HajjPackage::selectRaw("DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c")
            ->where('created_at', '>=', $months->first())
            ->groupBy('ym')->pluck('c', 'ym');

        return [
            'stats' => [
                'Total Packages' => $byStatus->sum('c'),
                'Active'         => (int) $byStatus->firstWhere('status', 'active')?->c,
                'Total Seats'    => number_format((int) $byStatus->sum('seats')),
                'Avg Price'      => currency_symbol() . number_format((float) HajjPackage::avg('price')),
            ],
            'donut' => [
                'labels' => $byStatus->pluck('status')->map(fn ($s) => ucfirst($s))->all(),
                'series' => $byStatus->pluck('c')->map(fn ($c) => (int) $c)->all(),
            ],
            'trend' => [
                'type'   => 'bar',
                'labels' => $months->map(fn ($m) => $m->format('M'))->all(),
                'series' => [
                    ['name' => 'New Packages', 'data' => $months->map(fn ($m) => (int) ($added[$m->format('Y-m')] ?? 0))->all()],
                ],
            ],
        ];
    }

    public function store(StoreHajjRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateHajjRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function hotelAllocation()
    {
        return view('backend.hajj.hotel-allocation', $this->repo->hotelAllocation());
    }

    public function flightAllocation()
    {
        return view('backend.hajj.flight-allocation', $this->repo->flightAllocation());
    }

    /** Hotel / flight / group / payment screens all post their edits here. */
    public function allocate(Request $request, $id)
    {
        $result = $this->repo->allocate($request, $id);

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }

    public function groups()
    {
        return view('backend.hajj.groups', $this->repo->groups());
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name'   => ['required', 'string', 'max:100'],
            'leader' => ['nullable', 'string', 'max:255'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $result = $this->repo->storeGroup($request);

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }

    public function deleteGroup($id)
    {
        $result = $this->repo->deleteGroup($id);

        return response()->json($result, $result['status_code']);
    }

    public function storeFlight(Request $request)
    {
        $request->validate([
            'flight_no'      => ['required', 'string', 'max:30'],
            'airline'        => ['nullable', 'string', 'max:255'],
            'departure_date' => ['nullable', 'date'],
            'return_date'    => ['nullable', 'date', 'after_or_equal:departure_date'],
            'seat_rows'      => ['nullable', 'integer', 'min:1', 'max:100'],
            'seat_letters'   => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z]+$/'],
        ]);

        $result = $this->repo->storeFlight($request);

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }

    public function deleteFlight($id)
    {
        $result = $this->repo->deleteFlight($id);

        return response()->json($result, $result['status_code']);
    }

    public function payments()
    {
        return view('backend.hajj.payments', $this->repo->payments());
    }

    public function documents()
    {
        return view('backend.hajj.documents', $this->repo->documents());
    }

    /** Set a pilgrim's document status straight from the verification list. */
    public function documentStatus(Request $request, $id)
    {
        $result = $this->repo->updateDocumentStatus($id, (string) $request->input('document_status'));

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }

    public function reports()
    {
        return view('backend.hajj.reports', $this->repo->reports());
    }
}
