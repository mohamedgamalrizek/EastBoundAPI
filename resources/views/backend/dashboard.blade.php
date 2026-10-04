@extends('backend.partials.master')
@section('title') Dashboard @endsection

@section('maincontent')
@php
    $statusBadge = fn($s) => in_array(strtolower($s), ['confirmed','paid','completed','active']) ? 'success' : (in_array(strtolower($s), ['pending','processing']) ? 'warning' : (strtolower($s) === 'cancelled' ? 'danger' : 'info'));
@endphp

<div class="container-fluid tv-dash">

    {{-- Greeting --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-black">Welcome back, {{ auth()->user()->name }} 👋</h4>
            <p class="text-muted mb-3 mb-md-0">Here's what's happening across your agency today.</p>
        </div>
        <x-how-it-works />
        <div class="tv-date-chip d-flex align-items-center"><i class="fa-solid fa-calendar-day mr-2"></i>{{ now()->format('l, d M Y') }}</div>
    </div>

    {{-- KPI cards (gradient + trend) --}}
    @php
        $kpis = [
            ['label'=>'Total Bookings','value'=>number_format($totalBookings),'icon'=>'fa-suitcase-rolling','grad'=>'linear-gradient(135deg,#6366f1,#8b5cf6)','delta'=>$bookingsDelta,'sub'=>'vs last month'],
            ['label'=>'Revenue','value'=>currency_symbol().number_format($revenue),'icon'=>'fa-sack-dollar','grad'=>'linear-gradient(135deg,#059669,#10b981)','delta'=>$revenueDelta,'sub'=>'vs last month'],
            ['label'=>'Visa Approvals','value'=>number_format($visaApproved),'icon'=>'fa-passport','grad'=>'linear-gradient(135deg,#0ea5e9,#06b6d4)','delta'=>null,'sub'=>'approved total'],
            ['label'=>'Open Leads','value'=>number_format($openLeads),'icon'=>'fa-user-plus','grad'=>'linear-gradient(135deg,#f59e0b,#f97316)','delta'=>null,'sub'=>'in pipeline'],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($kpis as $k)
        <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
            <div class="tv-kpi" style="--grad:linear-gradient(135deg,#A81A20,#D45B60)">
                <div class="tv-kpi-glow"></div>
                <div class="d-flex align-items-start justify-content-between">
                    <div class="tv-kpi-ico"><i class="fa-solid {{ $k['icon'] }}"></i></div>
                    @if(!is_null($k['delta']))
                        <span class="tv-trend {{ $k['delta'] >= 0 ? 'up' : 'down' }}">
                            <i class="fa-solid {{ $k['delta'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                            {{ abs($k['delta']) }}%
                        </span>
                    @endif
                </div>
                <div class="tv-kpi-val">{{ $k['value'] }}</div>
                <div class="tv-kpi-label">{{ $k['label'] }}</div>
                <div class="tv-kpi-sub">{{ $k['sub'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Revenue trend (area) + Bookings by status (donut) --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="tv-card h-100">
                <div class="tv-card-head">
                    <div>
                        <h5 class="mb-0">Revenue Overview</h5>
                        <span class="text-muted fs-13">Monthly performance · {{ now()->year }}</span>
                    </div>
                    <div class="tv-legend">
                        <span><i class="dot tv-dot-revenue"></i> Revenue</span>
                        <span><i class="dot tv-dot-bookings"></i> Bookings</span>
                    </div>
                </div>
                <div class="tv-card-body"><div id="revenueChart"></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h5 class="mb-0">Bookings by Status</h5></div>
                <div class="tv-card-body d-flex align-items-center"><div id="typeChart" class="w-100"></div></div>
            </div>
        </div>
    </div>

    {{-- Secondary mini-stats --}}
    @php
        $mini = [
            ['label'=>'Customers','value'=>number_format($customers),'icon'=>'fa-users','c'=>'#6366f1'],
            ['label'=>'Paid Revenue','value'=>currency_symbol().number_format($paidRevenue),'icon'=>'fa-circle-check','c'=>'#10b981'],
            ['label'=>'Avg. Booking','value'=>currency_symbol().number_format($avgBooking),'icon'=>'fa-receipt','c'=>'#0ea5e9'],
            ['label'=>'Conversion','value'=>$conversion.'%','icon'=>'fa-bullseye','c'=>'#f59e0b'],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($mini as $m)
        <div class="col-xl-3 col-md-6">
            <div class="tv-mini">
                <div class="tv-mini-ico" style="background:{{ $m['c'] }}1a;color:{{ $m['c'] }}"><i class="fa-solid {{ $m['icon'] }}"></i></div>
                <div>
                    <div class="tv-mini-val">{{ $m['value'] }}</div>
                    <div class="tv-mini-label">{{ $m['label'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Recent bookings + Top destinations --}}
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="tv-card h-100">
                <div class="tv-card-head">
                    <h5 class="mb-0">Recent Bookings</h5>
                    <a href="{{ route('booking.index') }}" class="tv-link">View all <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="tv-card-body">
                    <div class="table-responsive">
                        <table class="table tv-table mb-0">
                            <thead><tr><th>Booking</th><th>Customer</th><th>Package</th><th>Amount</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($recentBookings as $b)
                                <tr>
                                    <td><span class="fw-semibold">BKG-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="tv-avatar flex-shrink-0 mr-2">{{ strtoupper(mb_substr($b->customer_name ?? 'G', 0, 1)) }}</span>
                                            {{ $b->customer_name }}
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ optional($b->package)->title ?? '—' }}</td>
                                    <td class="fw-semibold">{{ currency_symbol() }}{{ number_format($b->amount) }}</td>
                                    <td><span class="bullet-badge bullet-badge-{{ $statusBadge($b->status) }}">{{ ucfirst($b->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No bookings yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h5 class="mb-0">Top Destinations</h5></div>
                <div class="tv-card-body">
                    @forelse($topDestinations as $d)
                        @php $pct = $totalBookings ? round($d->tours / $totalBookings * 100) : 0; @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold"><i class="fa-solid fa-location-dot text-primary me-1"></i>{{ $d->destination ?? 'Unknown' }}</span>
                                <span class="text-muted fs-13">{{ $d->tours }} · {{ currency_symbol() }}{{ number_format($d->revenue) }}</span>
                            </div>
                            <div class="tv-progress"><div class="tv-progress-bar" style="width:{{ $pct }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No booking data yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script type="application/json" id="flow-data">
{
  "charts": [
    {
      "target": "#revenueChart",
      "series": [
        { "name": "Revenue", "type": "area", "data": {!! json_encode($monthlyRevenue) !!} },
        { "name": "Bookings", "type": "line", "data": {!! json_encode($monthlyBookings) !!} }
      ],
      "categories": {!! json_encode($monthLabels) !!}
    },
    {
      "target": "#typeChart",
      "labels": {!! json_encode($bookingsByStatus->keys()) !!},
      "series": {!! json_encode($bookingsByStatus->values()) !!}
    }
  ]
}
</script>
@endpush
