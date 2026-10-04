@extends('backend.partials.master')
@section('title') Package Reports @endsection
@section('maincontent')
<x-page title="Package Reports" :breadcrumb="['Reports','Package Reports']">

    @include('backend.reports._filterbar')

    @php
        $laDonut = ['labels' => $byCategory->pluck('name')->all(),
                    'series' => $byCategory->pluck('revenue')->map(fn($v) => (float) $v)->all()];
        $laTrend = ['type' => 'bar',
                    'labels' => $byDestination->pluck('name')->all(),
                    'series' => [['name' => 'Revenue', 'data' => $byDestination->pluck('revenue')->map(fn($v) => (float) $v)->all()]]];
    @endphp
    <x-list-analytics title="Package breakdown" :donut="$laDonut" :trend="$laTrend" />

    <div class="row">
        <div class="col-md-3 col-6 "><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Packages</div><h3 class="mb-0 tv-fw-700">{{ number_format($totalPackages) }}</h3></div></div></div>
        <div class="col-md-3 col-6 "><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Bookings</div><h3 class="mb-0 tv-fw-700">{{ number_format($totalBookings) }}</h3></div></div></div>
        <div class="col-md-3 col-6 "><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Revenue</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($totalRevenue) }}</h3></div></div></div>
        <div class="col-md-3 col-6 "><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Destinations</div><h3 class="mb-0 tv-fw-700">{{ number_format($destinations) }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <h4 class="title-site mb-3">Bookings &amp; Revenue per Package</h4>
            <x-data-table :headers="['Package','Destination','Category','Price','Orders','Revenue']" :order="'[[5,&quot;desc&quot;]]'">
                @foreach($byPackage as $p)
                    <tr>
                        <td><b>{{ $p->title }}</b></td>
                        <td>{{ $p->destination }}</td>
                        <td><span class="bullet-badge bullet-badge-info">{{ $p->category }}</span></td>
                        <td>{{ currency_symbol() }}{{ number_format($p->price) }}</td>
                        <td>{{ number_format($p->orders) }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($p->revenue) }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <h4 class="title-site mb-3">By Destination</h4>
            <x-data-table :headers="['Destination','Packages','Orders','Revenue']" :order="'[[3,&quot;desc&quot;]]'">
                @foreach($byDestination as $d)
                    <tr><td>{{ $d->name }}</td><td>{{ number_format($d->packages) }}</td><td>{{ number_format($d->orders) }}</td><td>{{ currency_symbol() }}{{ number_format($d->revenue) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-6 mb-4">
            <h4 class="title-site mb-3">By Category</h4>
            <x-data-table :headers="['Category','Packages','Orders','Revenue']" :order="'[[3,&quot;desc&quot;]]'">
                @foreach($byCategory as $c)
                    <tr><td><span class="bullet-badge bullet-badge-info">{{ $c->name }}</span></td><td>{{ number_format($c->packages) }}</td><td>{{ number_format($c->orders) }}</td><td>{{ currency_symbol() }}{{ number_format($c->revenue) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
