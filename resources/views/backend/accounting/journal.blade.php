@extends('backend.partials.master')
@section('title') Journal Entries @endsection
@section('maincontent')
<x-page title="Journal Entries" :breadcrumb="['Accounting','Journal']">

@include('backend.accounting.partials._filters')

{{-- Each entry names both of its legs, so the journal reads the way a journal
     should: what was debited, what was credited, and where it came from. --}}
<x-data-table :headers="['Date','Reference','Description','Debit account','Credit account','Amount','Source']">
    @foreach($entries as $t)
        <tr>
            <td>{{ $t->txn_date?->format('Y-m-d') }}</td>
            <td><b>{{ $t->reference }}</b></td>
            <td>{{ $t->description }}</td>
            <td>{{ $t->debitAccountId() === $t->account_id ? ($t->account->name ?? $t->account_name) : ($t->contraAccount->name ?? '—') }}</td>
            <td>{{ $t->creditAccountId() === $t->account_id ? ($t->account->name ?? $t->account_name) : ($t->contraAccount->name ?? '—') }}</td>
            <td>{{ currency_symbol() }}{{ number_format($t->amount, 2) }}</td>
            <td>
                @if($t->isAutoPosted())
                    <span class="bullet-badge bullet-badge-info">{{ $t->sourceLabel() }}</span>
                @else
                    <span class="bullet-badge bullet-badge-warning">Manual</span>
                @endif
            </td>
        </tr>
    @endforeach
</x-data-table>

<div class="text-right mt-3"><b>Total posted: {{ currency_symbol() }}{{ number_format($total, 2) }}</b></div>

</x-page>
@endsection
