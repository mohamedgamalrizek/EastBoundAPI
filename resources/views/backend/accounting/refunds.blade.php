@extends('backend.partials.master')
@section('title') Refunds @endsection
@section('maincontent')
<x-page title="Refunds" :breadcrumb="['Accounting','Refunds']">

@include('backend.accounting.partials._filters')

@if(! $account)
    {{-- Refunds are whatever was posted to the Refunds account, not whatever
         happened to have "RFD-" typed into its reference. --}}
    <div class="alert alert-warning">
        No <b>Refunds</b> account exists yet. Add an Expense account for refunds in the
        Chart of Accounts and post refunds to it.
    </div>
@else
    <div class="text-muted mb-3 tv-text-sm">
        Posted to <b>{{ $account->code }} — {{ $account->name }}</b>
    </div>

    <x-data-table :headers="['Date','Reference','Description','Paid from','Amount']">
        @foreach($entries as $t)
            <tr>
                <td>{{ $t->txn_date?->format('Y-m-d') }}</td>
                <td><b>{{ $t->reference }}</b></td>
                <td>{{ $t->description }}</td>
                <td>{{ $t->contraAccount->name ?? '—' }}</td>
                <td class="text-danger">{{ currency_symbol() }}{{ number_format($t->amount, 2) }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <div class="text-right mt-3"><b>Total refunded: {{ currency_symbol() }}{{ number_format($total, 2) }}</b></div>
@endif

</x-page>
@endsection
