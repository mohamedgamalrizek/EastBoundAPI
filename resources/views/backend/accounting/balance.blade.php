@extends('backend.partials.master')
@section('title') Balance Sheet @endsection
@section('maincontent')
<x-page title="Balance Sheet" :breadcrumb="['Accounting','Balance Sheet']">

@include('backend.accounting.partials._filters')

<div class="row">
    <div class="col-12 mb-3">
        <div class="alert {{ $balanced ? 'alert-success' : 'alert-danger' }} mb-0">
            @if($balanced)
                <b>Balanced.</b> Both sides total {{ currency_symbol() }}{{ number_format($totalAssets, 2) }}
                @if($to) as at {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}@endif.
            @else
                <b>Out of balance by {{ currency_symbol() }}{{ number_format(abs($difference), 2) }}.</b>
                Assets {{ currency_symbol() }}{{ number_format($totalAssets, 2) }} vs liabilities + equity {{ currency_symbol() }}{{ number_format($totalLiabEquity, 2) }}.
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4"><div class="tv-card h-100"><div class="tv-card-head"><h4 class="title-site mb-0">Assets</h4></div><div class="tv-card-body">
        <table class="table"><tbody>
            @foreach($assets as $a)
                <tr><td>{{ $a['name'] }}</td><td class="text-right">{{ currency_symbol() }}{{ number_format($a['balance'], 2) }}</td></tr>
            @endforeach
            <tr class="fw-700"><td><b>Total Assets</b></td><td class="text-right"><b>{{ currency_symbol() }}{{ number_format($totalAssets, 2) }}</b></td></tr>
        </tbody></table>
    </div></div></div>

    <div class="col-lg-6 mb-4"><div class="tv-card h-100"><div class="tv-card-head"><h4 class="title-site mb-0">Liabilities & Equity</h4></div><div class="tv-card-body">
        <table class="table"><tbody>
            @foreach($liabilities as $a)
                <tr><td>{{ $a['name'] }}</td><td class="text-right">{{ currency_symbol() }}{{ number_format($a['balance'], 2) }}</td></tr>
            @endforeach
            @foreach($equity as $a)
                <tr><td>{{ $a['name'] }}</td><td class="text-right">{{ currency_symbol() }}{{ number_format($a['balance'], 2) }}</td></tr>
            @endforeach
            {{-- Profit earned but not yet closed into equity. Without this line
                 the two sides can never meet. --}}
            <tr>
                <td>Current Earnings <small class="text-muted">(income − expenses)</small></td>
                <td class="text-right {{ $earnings >= 0 ? 'text-success' : 'text-danger' }}">{{ currency_symbol() }}{{ number_format($earnings, 2) }}</td>
            </tr>
            <tr class="fw-700"><td><b>Total Liabilities & Equity</b></td><td class="text-right"><b>{{ currency_symbol() }}{{ number_format($totalLiabEquity, 2) }}</b></td></tr>
        </tbody></table>
    </div></div></div>
</div>
</x-page>
@endsection
