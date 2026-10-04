@extends('backend.partials.master')
@section('title') Flight Reports @endsection
@section('maincontent')
<x-page title="Flight Reports" :breadcrumb="['Reports','Flight Reports']">

    @include('backend.reports._filterbar')

    @php
        $laDonut = ['labels' => $byStatus->pluck('status')->all(),
                    'series' => $byStatus->pluck('total')->map(fn($v) => (int) $v)->all()];
        $laTrend = ['type' => 'bar',
                    'labels' => $byAirline->pluck('airline')->all(),
                    'series' => [['name' => 'Fare', 'data' => $byAirline->pluck('fare')->map(fn($v) => (float) $v)->all()]]];
    @endphp
    <x-list-analytics title="Flight breakdown" :donut="$laDonut" :trend="$laTrend" />

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Tickets</div><h3 class="mb-0 tv-fw-700">{{ number_format($total) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Fare</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($totalFare) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Confirmed</div><h3 class="mb-0 tv-fw-700">{{ number_format($confirmed) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Refunded / Cancelled</div><h3 class="mb-0 text-danger tv-fw-700">{{ number_format($refunded) }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-xl-5 mb-4">
            <h4 class="title-site mb-3">By Status</h4>
            <x-data-table :headers="['Status','Tickets','Fare']" :order="'[[1,&quot;desc&quot;]]'">
                @foreach($byStatus as $s)
                    <tr>
                        <td><span class="bullet-badge bullet-badge-{{ in_array($s->status, ['Cancelled','Refunded']) ? 'danger' : ($s->status === 'Pending' ? 'warning' : 'success') }}">{{ $s->status }}</span></td>
                        <td>{{ number_format($s->total) }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($s->fare) }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-7 mb-4">
            <h4 class="title-site mb-3">By Airline</h4>
            <x-data-table :headers="['Airline','Tickets','Fare']" :order="'[[2,&quot;desc&quot;]]'">
                @foreach($byAirline as $a)
                    <tr><td><b>{{ $a->airline }}</b></td><td>{{ number_format($a->total) }}</td><td>{{ currency_symbol() }}{{ number_format($a->fare) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
