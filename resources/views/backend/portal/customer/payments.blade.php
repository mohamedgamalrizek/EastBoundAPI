@extends('backend.partials.master')
@section('title') {{ ___('label.my_payments') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.my_payments') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.payments')]">

    <x-data-table :headers="[___('label.receipt'), ___('label.method'), ___('label.reference'), ___('label.date'), ___('label.amount'), '']">
        @foreach($payments as $p)
            <tr>
                <td><b>{{ $p->receipt_no }}</b></td>
                <td>{{ $p->method ?: '—' }}</td>
                <td>{{ $p->reference ?: '—' }}</td>
                <td>{{ $p->received_on?->format('d M Y') }}</td>
                <td>{{ currency_symbol() }}{{ number_format($p->amount) }}</td>
                <td><a href="{{ route('cust.document.pdf', ['kind' => 'receipt', 'id' => $p->id]) }}" class="btn btn-sm btn-outline-secondary">{{ ___('label.pdf') }}</a></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
