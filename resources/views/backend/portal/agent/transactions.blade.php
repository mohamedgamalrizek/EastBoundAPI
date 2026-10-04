@extends('backend.partials.master')
@section('title') {{ ___('menus.transactions') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.transactions') }}" :breadcrumb="[___('menus.agent_portal'), ___('menus.transactions')]">

    <x-data-table :headers="[___('label.txn'), ___('label.date'), ___('label.description'), ___('label.type'), ___('label.amount'), ___('label.balance'), ___('label.status')]">
        @foreach($transactions as $t)
            @php $cls = $t->type === 'credit' ? 'success' : 'danger'; $sign = $t->type === 'credit' ? '+' : '-'; @endphp
            <tr>
                <td><b>{{ $t->reference }}</b></td>
                <td>{{ $t->txn_date?->format('d M Y') }}</td>
                <td>{{ $t->description }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $cls }}">{{ ucfirst($t->type) }}</span></td>
                <td>{{ $sign }}{{ currency_symbol() }}{{ number_format($t->amount) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($t->balance_after) }}</td>
                <td><span class="bullet-badge bullet-badge-success">{{ ___('label.success') }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
