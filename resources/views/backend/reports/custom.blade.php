@extends('backend.partials.master')
@section('title') Custom Reports @endsection
@section('maincontent')
<x-page title="Custom Reports" :breadcrumb="['Reports','Custom']">

    @include('backend.reports._filterbar')

    @php
        $laDonut = ['labels' => ['Income', 'Expense'],
                    'series' => [(float) $income, (float) $expense]];
        $laTrend = ['type' => 'bar',
                    'labels' => $snapshot->pluck('module')->all(),
                    'series' => [['name' => 'Records', 'data' => $snapshot->pluck('records')->map(fn($v) => (int) $v)->all()]]];
    @endphp
    <x-list-analytics title="Workspace snapshot" :donut="$laDonut" :trend="$laTrend" />

    <div class="row">
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Records</div><h3 class="mb-0 tv-fw-700">{{ number_format($totalRecords) }}</h3></div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Income</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($income) }}</h3></div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Expense</div><h3 class="mb-0 text-danger tv-fw-700">{{ currency_symbol() }}{{ number_format($expense) }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-12">
            <h4 class="title-site mb-3">Cross-Module Snapshot</h4>
            <x-data-table :headers="['Module','Records','Metric','Value']" :order="'[[1,&quot;desc&quot;]]'">
                @foreach($snapshot as $row)
                    <tr>
                        <td><b>{{ $row->module }}</b></td>
                        <td>{{ number_format($row->records) }}</td>
                        <td>{{ $row->label }}</td>
                        <td>{{ is_null($row->value) ? '—' : currency_symbol() . number_format($row->value) }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
