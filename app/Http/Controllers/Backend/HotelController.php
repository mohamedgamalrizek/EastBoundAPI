<?php

namespace App\Http\Controllers\Backend;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Repositories\Hotel\HotelInterface;
use App\Http\Requests\Hotel\StoreHotelRequest;
use App\Http\Requests\Hotel\UpdateHotelRequest;
use App\Http\Controllers\Backend\Concerns\BuildsListAnalytics;

class HotelController extends BaseCrudController
{
    use BuildsListAnalytics;

    protected string $viewPath      = 'backend.hotel';
    protected string $redirectRoute = 'hotel.index';

    public function __construct(HotelInterface $repo)
    {
        $this->repo = $repo;
    }

    protected function listAnalytics(Request $request): ?array
    {
        $byCity = Hotel::selectRaw('city, COUNT(*) c')->groupBy('city')->orderByDesc('c')->take(8)->get();

        return [
            'stats' => [
                'Total Hotels' => Hotel::count(),
                'Total Rooms'  => number_format((int) Hotel::sum('rooms_count')),
                'Cities'       => (int) Hotel::distinct('city')->count('city'),
                'Avg / Night'  => currency_symbol() . number_format((float) Hotel::avg('price_per_night')),
            ],
            'donut' => $this->laGroup(Hotel::class, 'status'),
            'trend' => [
                'type'   => 'bar',
                'labels' => $byCity->pluck('city')->all(),
                'series' => [['name' => 'Hotels', 'data' => $byCity->pluck('c')->map(fn ($v) => (int) $v)->all()]],
            ],
        ];
    }

    public function store(StoreHotelRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateHotelRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function details($id)
    {
        return view('backend.hotel.details', $this->repo->details($id));
    }

    public function availability()
    {
        return view('backend.hotel.availability', $this->repo->availability());
    }

    public function vouchers()
    {
        return view('backend.hotel.vouchers', $this->repo->vouchers());
    }

    /**
     * Printable voucher for a single confirmed booking. Rendered as a
     * standalone page that auto-opens the browser print dialog, so it can be
     * saved as PDF without a server-side PDF renderer.
     */
    public function voucherPdf($id)
    {
        $booking = \App\Models\HotelBooking::with(['hotel', 'hotelRoom'])
            ->where('status', 'Confirmed')
            ->find($id);

        if (! $booking) {
            return redirect()->route('hotel.vouchers')
                ->with('error', ___('alert.not_found'));
        }

        return view('backend.hotel.voucher-print', [
            'booking'   => $booking,
            'voucherNo' => 'VCH-' . str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT),
        ]);
    }

    public function reports()
    {
        return view('backend.hotel.reports', $this->repo->reports());
    }
}
