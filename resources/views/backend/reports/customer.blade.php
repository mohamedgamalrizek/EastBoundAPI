@extends('backend.partials.master')
@section('title') Customer Reports @endsection
@section('maincontent')
<x-page title="Customer Reports" :breadcrumb="['Reports','Customer Reports']">

    @include('backend.reports._filterbar')

    @php
        $laDonut = ['labels' => $byTier->pluck('tier')->all(),
                    'series' => $byTier->pluck('total')->map(fn($v) => (int) $v)->all()];
        $laTrend = ['type' => 'bar',
                    'labels' => $byStatus->pluck('status')->map(fn($s) => ucfirst($s))->all(),
                    'series' => [['name' => 'Customers', 'data' => $byStatus->pluck('total')->map(fn($v) => (int) $v)->all()]]];
    @endphp
    <x-list-analytics title="Customer breakdown" :donut="$laDonut" :trend="$laTrend" />

    <div class="row">
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Customers</div><h3 class="mb-0 tv-fw-700">{{ number_format($total) }}</h3></div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Active</div><h3 class="mb-0 text-success tv-fw-700">{{ number_format($active) }}</h3></div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Inactive</div><h3 class="mb-0 text-danger tv-fw-700">{{ number_format($inactive) }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <h4 class="title-site mb-3">By Tier</h4>
            <x-data-table :headers="['Tier','Total']" :order="'[[1,&quot;desc&quot;]]'">
                @foreach($byTier as $t)
                    <tr><td><span class="bullet-badge bullet-badge-info">{{ ucfirst($t->tier) }}</span></td><td>{{ number_format($t->total) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-6 mb-4">
            <h4 class="title-site mb-3">By Status</h4>
            <x-data-table :headers="['Status','Total']" :order="'[[1,&quot;desc&quot;]]'">
                @foreach($byStatus as $s)
                    <tr><td><span class="bullet-badge bullet-badge-{{ $s->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($s->status) }}</span></td><td>{{ number_format($s->total) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
