@extends('backend.partials.master')
@section('title') Sales Reports @endsection
@section('maincontent')
<x-page title="Sales Reports" :breadcrumb="['Reports','Sales Reports']">

    @include('backend.reports._filterbar')

    @php
        $laDonut = ['labels' => $byStatus->pluck('status')->map(fn($s) => ucfirst($s))->all(),
                    'series' => $byStatus->pluck('revenue')->map(fn($v) => (float) $v)->all()];
        $laTrend = ['type' => 'bar',
                    'labels' => $topPackages->map(fn($p) => $p->package->title ?? '—')->all(),
                    'series' => [['name' => 'Revenue', 'data' => $topPackages->pluck('revenue')->map(fn($v) => (float) $v)->all()]]];
    @endphp
    <x-list-analytics title="Sales breakdown" :donut="$laDonut" :trend="$laTrend" />

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Sales</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($totalSales) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Orders</div><h3 class="mb-0 tv-fw-700">{{ number_format($orders) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Avg Order</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($avgOrder) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Statuses</div><h3 class="mb-0 tv-fw-700">{{ $byStatus->count() }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-xl-5 mb-4">
            <h4 class="title-site mb-3">Sales by Status</h4>
            <x-data-table :headers="['Status','Orders','Revenue']" :order="'[[2,&quot;desc&quot;]]'">
                @foreach($byStatus as $s)
                    <tr>
                        <td><span class="bullet-badge bullet-badge-{{ \Illuminate\Support\Str::contains(strtolower($s->status), ['cancel','refund','reject']) ? 'danger' : (\Illuminate\Support\Str::contains(strtolower($s->status), ['pend','process','review']) ? 'warning' : 'success') }}">{{ $s->status }}</span></td>
                        <td>{{ number_format($s->orders) }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($s->revenue) }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-7 mb-4">
            <h4 class="title-site mb-3">Top Packages by Revenue</h4>
            <x-data-table :headers="['Package','Destination','Orders','Revenue']" :order="'[[3,&quot;desc&quot;]]'">
                @foreach($topPackages as $p)
                    <tr>
                        <td><b>{{ $p->package->title ?? '—' }}</b></td>
                        <td>{{ $p->package->destination ?? '—' }}</td>
                        <td>{{ number_format($p->orders) }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($p->revenue) }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
