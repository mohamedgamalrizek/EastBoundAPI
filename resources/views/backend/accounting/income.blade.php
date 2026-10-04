@extends('backend.partials.master')
@section('title') Income @endsection
@section('maincontent')
<x-page title="Income" :breadcrumb="['Accounting','Income']">

@include('backend.accounting.partials._filters')

{{-- Every leg that landed on an Income account — invoices post themselves
     here, so this is the agency's real revenue, not a hand-kept list. --}}
<x-data-table :headers="['Date','Source','Income account','Received into','Reference','Amount']">
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['txn']->txn_date?->format('Y-m-d') }}</td>
            <td>{{ $row['txn']->description }}</td>
            <td>{{ $row['account_name'] }}</td>
            <td>{{ $row['against'] }}</td>
            <td><b>{{ $row['txn']->reference }}</b></td>
            <td class="{{ $row['credit'] ? 'text-success' : 'text-danger' }}">
                {{ $row['credit'] ? currency_symbol().number_format($row['credit'], 2) : '−'.currency_symbol().number_format($row['debit'], 2) }}
            </td>
        </tr>
    @endforeach
</x-data-table>

<div class="text-right mt-3"><b>Total income: {{ currency_symbol() }}{{ number_format($total, 2) }}</b></div>

</x-page>
@endsection
