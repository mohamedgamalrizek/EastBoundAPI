@extends('backend.partials.master')
@section('title') {{ ___('label.my_wallet') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.my_wallet') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.wallet')]">

    <div class="row">
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.balance') }}</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($balance) }}</h3></div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.earned') }}</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($earned) }}</h3></div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.spent') }}</div><h3 class="mb-0 text-danger tv-fw-700">{{ currency_symbol() }}{{ number_format($spent) }}</h3></div></div></div>
    </div>

    <x-data-table :headers="[___('label.reference'), ___('label.date'), ___('label.description'), ___('label.type'), ___('label.amount'), ___('label.balance_after')]">
        @foreach($transactions as $t)
            @php $c = $t->type === 'credit' ? 'success' : 'danger'; $sign = $t->type === 'credit' ? '+' : '-'; @endphp
            <tr>
                <td><b>{{ $t->reference }}</b></td>
                <td>{{ $t->txn_date?->format('d M Y') }}</td>
                <td>{{ $t->description }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($t->type) }}</span></td>
                <td>{{ $sign }}{{ currency_symbol() }}{{ number_format($t->amount) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($t->balance_after) }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
