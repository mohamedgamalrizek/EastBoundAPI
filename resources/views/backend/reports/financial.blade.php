@extends('backend.partials.master')
@section('title') Financial Reports @endsection
@section('maincontent')
<x-page title="Financial Reports" :breadcrumb="['Reports','Financial Reports']">

    @include('backend.reports._filterbar')

    @php
        $laDonut = ['labels' => ['Income', 'Expense'],
                    'series' => [(float) $income, (float) $expense]];
        $laTrend = ['type' => 'area',
                    'labels' => $byMonth->pluck('month')->all(),
                    'series' => [
                        ['name' => 'Income',  'data' => $byMonth->pluck('income')->map(fn($v) => (float) $v)->all()],
                        ['name' => 'Expense', 'data' => $byMonth->pluck('expense')->map(fn($v) => (float) $v)->all()],
                    ]];
    @endphp
    <x-list-analytics title="Income vs Expense" :donut="$laDonut" :trend="$laTrend" />

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Income</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($income) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Expense</div><h3 class="mb-0 text-danger tv-fw-700">{{ currency_symbol() }}{{ number_format($expense) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Net</div><h3 class="mb-0 {{ $net >= 0 ? 'text-success' : 'text-danger' }} tv-fw-700">{{ currency_symbol() }}{{ number_format($net) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Transactions</div><h3 class="mb-0 tv-fw-700">{{ number_format($txns) }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">Income vs Expense by Month</h4></div>
                <div class="tv-card-body"><div id="rsFin"></div></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h4 class="title-site mb-3">By Account</h4>
            <x-data-table :headers="['Account','Income','Expense','Net']" :order="'[[1,&quot;desc&quot;]]'">
                @foreach($byAccount as $a)
                    <tr>
                        <td><b>{{ $a->account_name }}</b></td>
                        <td>{{ currency_symbol() }}{{ number_format($a->income) }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($a->expense) }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($a->income - $a->expense) }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
@push('scripts')
<script type="application/json" id="flow-data">
{
  "charts": [
    {
      "target": "#rsFin",
      "series": [
        { "name": "Income", "data": {!! json_encode($byMonth->pluck('income')->map(fn($v)=>(float)$v)) !!} },
        { "name": "Expense", "data": {!! json_encode($byMonth->pluck('expense')->map(fn($v)=>(float)$v)) !!} }
      ],
      "categories": {!! json_encode($byMonth->pluck('month')) !!}
    }
  ]
}
</script>
@endpush
