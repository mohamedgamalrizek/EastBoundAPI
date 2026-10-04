@extends('backend.partials.master')
@section('title') Agent Reports @endsection
@section('maincontent')
<x-page title="Agent Reports" :breadcrumb="['Reports','Agent Reports']">

    @include('backend.reports._filterbar')

    @php
        $laDonut = ['labels' => $byStatus->pluck('status')->map(fn($s) => ucfirst($s))->all(),
                    'series' => $byStatus->pluck('amount')->map(fn($v) => (float) $v)->all()];
        $laTrend = ['type' => 'bar',
                    'labels' => $byStatus->pluck('status')->map(fn($s) => ucfirst($s))->all(),
                    'series' => [['name' => 'Records', 'data' => $byStatus->pluck('records')->map(fn($v) => (int) $v)->all()]]];
    @endphp
    <x-list-analytics title="Commission breakdown" :donut="$laDonut" :trend="$laTrend" />

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Commission</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($totalCommission) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Paid</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($paid) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Pending</div><h3 class="mb-0 text-warning tv-fw-700">{{ currency_symbol() }}{{ number_format($pending) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Records</div><h3 class="mb-0 tv-fw-700">{{ number_format($records) }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-xl-4 mb-4">
            <h4 class="title-site mb-3">By Status</h4>
            <x-data-table :headers="['Status','Records','Amount']" :order="'[[2,&quot;desc&quot;]]'">
                @foreach($byStatus as $s)
                    <tr>
                        <td><span class="bullet-badge bullet-badge-{{ $s->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($s->status) }}</span></td>
                        <td>{{ number_format($s->records) }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($s->amount) }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-8 mb-4">
            <h4 class="title-site mb-3">Recent Commissions</h4>
            <x-data-table :headers="['Reference','Customer','Amount','Status','Earned On']" :order="'[]'">
                @foreach($recent as $r)
                    <tr>
                        <td><b>{{ $r->reference }}</b></td>
                        <td>{{ $r->customer_name }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($r->amount) }}</td>
                        <td><span class="bullet-badge bullet-badge-{{ $r->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($r->status) }}</span></td>
                        <td>{{ optional($r->earned_on)->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
