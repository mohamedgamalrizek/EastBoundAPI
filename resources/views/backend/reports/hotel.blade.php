@extends('backend.partials.master')
@section('title') Hotel Reports @endsection
@section('maincontent')
<x-page title="Hotel Reports" :breadcrumb="['Reports','Hotel Reports']">

    @include('backend.reports._filterbar')

    @php
        $laDonut = ['labels' => $byCategory->pluck('category')->all(),
                    'series' => $byCategory->pluck('hotels')->map(fn($v) => (int) $v)->all()];
        $laTrend = ['type' => 'bar',
                    'labels' => $byCity->pluck('city')->all(),
                    'series' => [['name' => 'Hotels', 'data' => $byCity->pluck('hotels')->map(fn($v) => (int) $v)->all()]]];
    @endphp
    <x-list-analytics title="Hotel breakdown" :donut="$laDonut" :trend="$laTrend" />

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Hotels</div><h3 class="mb-0 tv-fw-700">{{ number_format($total) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Rooms</div><h3 class="mb-0 tv-fw-700">{{ number_format($rooms) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Cities</div><h3 class="mb-0 tv-fw-700">{{ number_format($cities) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Avg Price / Night</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($avgPrice) }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-xl-7 mb-4">
            <h4 class="title-site mb-3">By City</h4>
            <x-data-table :headers="['City','Country','Hotels','Rooms','Avg Price']" :order="'[[2,&quot;desc&quot;]]'">
                @foreach($byCity as $c)
                    <tr><td><b>{{ $c->city }}</b></td><td>{{ $c->country }}</td><td>{{ number_format($c->hotels) }}</td><td>{{ number_format($c->rooms) }}</td><td>{{ currency_symbol() }}{{ number_format($c->avg_price) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-5 mb-4">
            <h4 class="title-site mb-3">By Star Category</h4>
            <x-data-table :headers="['Category','Hotels','Rooms','Avg Price']" :order="'[[0,&quot;desc&quot;]]'">
                @foreach($byCategory as $c)
                    <tr><td><span class="bullet-badge bullet-badge-warning">{{ $c->category }} Star</span></td><td>{{ number_format($c->hotels) }}</td><td>{{ number_format($c->rooms) }}</td><td>{{ currency_symbol() }}{{ number_format($c->avg_price) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
