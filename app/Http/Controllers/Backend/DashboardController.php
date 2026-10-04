<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Models\Lead;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\VisaApplication;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $now  = Carbon::now();
        $year = $now->year;

        $bookingsByStatus = Booking::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        // Top destinations by booking count (join packages)
        $topDestinations = Booking::selectRaw('packages.destination, count(*) as tours, sum(bookings.amount) as revenue')
            ->join('packages', 'bookings.package_id', '=', 'packages.id')
            ->groupBy('packages.destination')
            ->orderByDesc('tours')
            ->limit(5)
            ->get();

        // Monthly revenue + bookings trend (driven by travel_date) for the current year.
        $raw = Booking::selectRaw('MONTH(travel_date) as m, SUM(amount) as revenue, COUNT(*) as cnt')
            ->whereYear('travel_date', $year)
            ->groupBy('m')
            ->pluck('revenue', 'm');
        $rawCnt = Booking::selectRaw('MONTH(travel_date) as m, COUNT(*) as cnt')
            ->whereYear('travel_date', $year)
            ->groupBy('m')
            ->pluck('cnt', 'm');

        $monthLabels = $monthlyRevenue = $monthlyBookings = [];
        foreach (range(1, 12) as $m) {
            $monthLabels[]    = Carbon::create($year, $m, 1)->format('M');
            $monthlyRevenue[] = (int) ($raw[$m] ?? 0);
            $monthlyBookings[] = (int) ($rawCnt[$m] ?? 0);
        }

        // Month-over-month deltas (travel_date based).
        $thisM = $now->month;
        $lastM = $now->copy()->subMonth()->month;
        $bThis = (int) ($rawCnt[$thisM] ?? 0);
        $bLast = (int) ($rawCnt[$lastM] ?? 0);
        $rThis = (int) ($raw[$thisM] ?? 0);
        $rLast = (int) ($raw[$lastM] ?? 0);

        $delta = fn ($cur, $prev) => $prev > 0 ? round(($cur - $prev) / $prev * 100) : ($cur > 0 ? 100 : 0);

        $totalBookings = Booking::count();
        $revenue       = (int) Booking::sum('amount');
        $customers     = Customer::count();
        $paidRevenue   = (int) Booking::whereIn('status', ['paid', 'confirmed'])->sum('amount');

        return view('backend.dashboard', [
            'totalBookings'    => $totalBookings,
            'revenue'          => $revenue,
            'visaApproved'     => VisaApplication::where('status', 'Approved')->count(),
            'openLeads'        => Lead::count(),
            'customers'        => $customers,
            'paidRevenue'      => $paidRevenue,
            'avgBooking'       => $totalBookings ? (int) round($revenue / $totalBookings) : 0,
            'conversion'       => $customers ? round($totalBookings / $customers * 100) : 0,
            'recentBookings'   => Booking::with('package')->latest()->take(6)->get(),
            'bookingsByStatus' => $bookingsByStatus,
            'topDestinations'  => $topDestinations,
            // trend
            'monthLabels'      => $monthLabels,
            'monthlyRevenue'   => $monthlyRevenue,
            'monthlyBookings'  => $monthlyBookings,
            'bookingsDelta'    => $delta($bThis, $bLast),
            'revenueDelta'     => $delta($rThis, $rLast),
        ]);
    }
}
