@extends('backend.partials.master')
@section('title') Expenses @endsection
@section('maincontent')
<x-page title="Expenses" :breadcrumb="['Accounting','Expenses']">

@include('backend.accounting.partials._filters')

<x-data-table :headers="['Date','Description','Expense account','Paid from','Reference','Amount']">
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['txn']->txn_date?->format('Y-m-d') }}</td>
            <td>{{ $row['txn']->description }}</td>
            <td>{{ $row['account_name'] }}</td>
            <td>{{ $row['against'] }}</td>
            <td><b>{{ $row['txn']->reference }}</b></td>
            <td class="{{ $row['debit'] ? 'text-danger' : 'text-success' }}">
                {{ $row['debit'] ? currency_symbol().number_format($row['debit'], 2) : '−'.currency_symbol().number_format($row['credit'], 2) }}
            </td>
        </tr>
    @endforeach
</x-data-table>

<div class="text-right mt-3"><b>Total expenses: {{ currency_symbol() }}{{ number_format($total, 2) }}</b></div>

</x-page>
@endsection
