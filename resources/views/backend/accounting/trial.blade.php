@extends('backend.partials.master')
@section('title') Trial Balance @endsection
@section('maincontent')
<x-page title="Trial Balance" :breadcrumb="['Accounting','Trial Balance']">

@include('backend.accounting.partials._filters')

{{-- A trial balance is only worth reading if it proves itself, so the check
     sits at the top of the page rather than being left to the reader. --}}
<div class="row">
    <div class="col-12 mb-3">
        <div class="alert {{ $balanced ? 'alert-success' : 'alert-danger' }} mb-0">
            @if($balanced)
                <b>Balanced.</b> Debits and credits both total {{ currency_symbol() }}{{ number_format($totalDebit, 2) }}.
            @else
                <b>Out of balance by {{ currency_symbol() }}{{ number_format(abs($difference), 2) }}.</b>
                Debits {{ currency_symbol() }}{{ number_format($totalDebit, 2) }} vs credits {{ currency_symbol() }}{{ number_format($totalCredit, 2) }} —
                some entry is missing its second leg.
            @endif
        </div>
    </div>
</div>

<div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body"><div class="table-responsive">
<table class="table table-responsive-sm">
    <thead class="bg">
        <tr>
            <th>Code</th><th>Account</th><th>Type</th>
            <th class="text-right">Opening</th>
            <th class="text-right">Debit</th>
            <th class="text-right">Credit</th>
            <th class="text-right">Closing (Dr)</th>
            <th class="text-right">Closing (Cr)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $r)
            <tr>
                <td>{{ $r['account']->code }}</td>
                <td>{{ $r['account']->name }}</td>
                <td><span class="bullet-badge bullet-badge-info">{{ $r['account']->type }}</span></td>
                <td class="text-right">{{ currency_symbol() }}{{ number_format($r['opening'], 2) }}</td>
                <td class="text-right">{{ $r['debit'] ? currency_symbol().number_format($r['debit'], 2) : '—' }}</td>
                <td class="text-right">{{ $r['credit'] ? currency_symbol().number_format($r['credit'], 2) : '—' }}</td>
                <td class="text-right">{{ $r['is_debit'] ? currency_symbol().number_format(abs($r['closing']), 2) : '—' }}</td>
                <td class="text-right">{{ $r['is_debit'] ? '—' : currency_symbol().number_format(abs($r['closing']), 2) }}</td>
            </tr>
        @endforeach
        <tr class="fw-700">
            <td colspan="3"><b>Total</b></td>
            <td class="text-right">—</td>
            <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($movementDebit, 2) }}</b></td>
            <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($movementCredit, 2) }}</b></td>
            <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($totalDebit, 2) }}</b></td>
            <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($totalCredit, 2) }}</b></td>
        </tr>
    </tbody>
</table>
</div></div></div></div></div>
</x-page>
@endsection
