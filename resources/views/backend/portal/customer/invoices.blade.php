@extends('backend.partials.master')
@section('title') {{ ___('label.my_invoices') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.my_invoices') }}" :breadcrumb="[___('permissions.customer_portal'), ___('label.invoices')]">

    <x-data-table :headers="[___('label.invoice'), ___('label.issued'), ___('label.due_date'), ___('label.amount'), ___('label.paid'), ___('label.due'), ___('label.status'), '']">
        @foreach($invoices as $i)
            @php
                $class = match(strtolower($i->status)) {
                    'paid'     => 'success',
                    'overdue'  => 'danger',
                    'refunded' => 'info',
                    default    => 'warning',
                };
                $due = $i->dueAmount();
            @endphp
            <tr>
                <td><b>{{ $i->invoice_no }}</b></td>
                <td>{{ $i->issue_date?->format('d M Y') }}</td>
                <td>{{ $i->due_date?->format('d M Y') ?: '—' }}</td>
                <td>{{ currency_symbol() }}{{ number_format($i->amount, 2) }}</td>
                <td class="text-success">{{ currency_symbol() }}{{ number_format($i->paid_amount, 2) }}</td>
                <td class="{{ $due > 0 ? 'text-danger' : 'text-muted' }}">{{ currency_symbol() }}{{ number_format(max(0, $due), 2) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $class }}">{{ ucfirst($i->status) }}</span></td>
                <td><a href="{{ route('cust.document.pdf', ['kind' => 'invoice', 'id' => $i->id]) }}" class="btn btn-sm btn-outline-secondary">{{ ___('label.pdf') }}</a></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
