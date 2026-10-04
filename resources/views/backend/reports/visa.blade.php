@extends('backend.partials.master')
@section('title') Visa Reports @endsection
@section('maincontent')
<x-page title="Visa Reports" :breadcrumb="['Reports','Visa Reports']">

    @include('backend.reports._filterbar')

    @php
        $laDonut = ['labels' => $byStatus->pluck('status')->all(),
                    'series' => $byStatus->pluck('total')->map(fn($v) => (int) $v)->all()];
        $laTrend = ['type' => 'bar',
                    'labels' => $byCountry->pluck('country')->all(),
                    'series' => [['name' => 'Applications', 'data' => $byCountry->pluck('total')->map(fn($v) => (int) $v)->all()]]];
    @endphp
    <x-list-analytics title="Visa breakdown" :donut="$laDonut" :trend="$laTrend" />

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Applications</div><h3 class="mb-0 tv-fw-700">{{ number_format($total) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Approved</div><h3 class="mb-0 text-success tv-fw-700">{{ number_format($approved) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Processing</div><h3 class="mb-0 text-warning tv-fw-700">{{ number_format($processing) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Rejected</div><h3 class="mb-0 text-danger tv-fw-700">{{ number_format($rejected) }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-xl-4 mb-4">
            <h4 class="title-site mb-3">By Status</h4>
            <x-data-table :headers="['Status','Total']" :order="'[[1,&quot;desc&quot;]]'">
                @foreach($byStatus as $s)
                    <tr>
                        <td><span class="bullet-badge bullet-badge-{{ $s->status === 'Approved' ? 'success' : ($s->status === 'Rejected' ? 'danger' : 'warning') }}">{{ $s->status }}</span></td>
                        <td>{{ number_format($s->total) }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-4 mb-4">
            <h4 class="title-site mb-3">By Country</h4>
            <x-data-table :headers="['Country','Total']" :order="'[[1,&quot;desc&quot;]]'">
                @foreach($byCountry as $c)
                    <tr><td>{{ $c->country }}</td><td>{{ number_format($c->total) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-4 mb-4">
            <h4 class="title-site mb-3">By Visa Type</h4>
            <x-data-table :headers="['Visa Type','Total']" :order="'[[1,&quot;desc&quot;]]'">
                @foreach($byType as $t)
                    <tr><td>{{ $t->visa_type }}</td><td>{{ number_format($t->total) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
