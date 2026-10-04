@extends('backend.partials.master')
@section('title') Tax Reports @endsection
@section('maincontent')
<x-page title="Tax Reports" :breadcrumb="['Accounting','Tax Reports']">

@include('backend.accounting.partials._filters')

<div class="row">
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Taxable Sales</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($taxableSales, 2) }}</h3></div></div></div>
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">VAT ({{ rtrim(rtrim(number_format($vatRate, 2), '0'), '.') }}%)</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($vatCollected, 2) }}</h3></div></div></div>
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">VAT Payable (ledger)</div><h3 class="mb-0 text-danger tv-fw-700">{{ currency_symbol() }}{{ number_format($vatPayable, 2) }}</h3></div></div></div>
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">AIT ({{ rtrim(rtrim(number_format($aitRate, 2), '0'), '.') }}%)</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($ait, 2) }}</h3></div></div></div>
</div>

{{-- Rates come from Settings (vat_rate / ait_rate), so a rate change is a
     settings edit rather than a code change. --}}
<div class="text-muted mb-3 tv-text-sm">
    Rates: VAT {{ rtrim(rtrim(number_format($vatRate, 2), '0'), '.') }}%,
    AIT {{ rtrim(rtrim(number_format($aitRate, 2), '0'), '.') }}% —
    change them in Settings (<code>vat_rate</code>, <code>ait_rate</code>).
    Net tax on this period: <b>{{ currency_symbol() }}{{ number_format($netTax, 2) }}</b>.
</div>

<div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body"><div class="table-responsive">
<table class="table">
    <thead class="bg"><tr><th>Period</th><th class="text-right">Taxable Sales</th><th class="text-right">VAT</th><th class="text-right">AIT</th><th class="text-right">Total</th></tr></thead>
    <tbody>
        @forelse($periods as $p)
            <tr>
                <td>{{ $p['period'] }}</td>
                <td class="text-right">{{ currency_symbol() }}{{ number_format($p['sales'], 2) }}</td>
                <td class="text-right">{{ currency_symbol() }}{{ number_format($p['vat'], 2) }}</td>
                <td class="text-right">{{ currency_symbol() }}{{ number_format($p['ait'], 2) }}</td>
                <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($p['vat'] + $p['ait'], 2) }}</b></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted text-center">No taxable sales in this period.</td></tr>
        @endforelse
    </tbody>
</table>
</div></div></div></div></div>
</x-page>
@endsection
