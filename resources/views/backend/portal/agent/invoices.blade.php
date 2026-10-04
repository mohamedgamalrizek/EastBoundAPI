@extends('backend.partials.master')
@section('title') {{ ___('menus.agent_invoices') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.agent_invoices') }}" :breadcrumb="[___('menus.agent_portal'), ___('label.invoices')]">

    <x-data-table :headers="[___('label.invoice'), ___('label.customer'), ___('label.issued'), ___('label.due'), ___('label.amount'), ___('label.status')]">
        @foreach($invoices as $i)
            @php $cls = $i->status === 'paid' ? 'success' : ($i->status === 'overdue' ? 'danger' : 'warning'); @endphp
            <tr>
                <td><b>{{ $i->invoice_no }}</b></td>
                <td>{{ $i->customer_name }}</td>
                <td>{{ $i->issued_on?->format('d M Y') }}</td>
                <td>{{ $i->due_on?->format('d M Y') }}</td>
                <td>{{ currency_symbol() }}{{ number_format($i->amount) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $cls }}">{{ ucfirst($i->status) }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
