@extends('backend.partials.master')
@section('title') Accounting Dashboard @endsection
@section('maincontent')
<x-page title="Accounting Dashboard" :breadcrumb="['Accounting','Dashboard']">

@include('backend.accounting.partials._filters')

<div class="row">
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Income</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($income, 2) }}</h3></div></div></div>
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Expense</div><h3 class="mb-0 text-danger tv-fw-700">{{ currency_symbol() }}{{ number_format($expense, 2) }}</h3></div></div></div>
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Net Profit</div><h3 class="mb-0 {{ $profit >= 0 ? 'text-success' : 'text-danger' }} tv-fw-700">{{ currency_symbol() }}{{ number_format($profit, 2) }}</h3></div></div></div>
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Cash &amp; Bank</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($cash, 2) }}</h3></div></div></div>
</div>
<div class="row">
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Receivable (unpaid invoices)</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($receivable, 2) }}</h3></div></div></div>
</div>
<div class="row">
    <div class="col-xl-8"><div class="tv-card h-100"><div class="tv-card-head"><h4 class="title-site mb-0">Income vs Expense</h4></div><div class="tv-card-body"><div id="accChart"></div></div></div></div>
    <div class="col-xl-4"><div class="tv-card h-100"><div class="tv-card-head"><h4 class="title-site mb-0">Expense Split</h4></div><div class="tv-card-body"><div id="accDonut"></div></div></div></div>
</div>
</x-page>
@endsection
@push('scripts')
<script type="application/json" id="flow-data">
{
  "charts": [
    {
      "target": "#accChart",
      "series": [
        { "name": "Income", "data": @json($incomeSeries) },
        { "name": "Expense", "data": @json($expenseSeries) }
      ],
      "categories": @json($months)
    },
    {
      "target": "#accDonut",
      "labels": @json($expenseSplit->keys()),
      "series": @json($expenseSplit->values()->map(fn($v)=>(float)$v))
    }
  ]
}
</script>
@endpush
