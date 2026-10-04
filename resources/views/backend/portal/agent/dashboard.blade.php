@extends('backend.partials.master')
@section('title') {{ ___('menus.agent_dashboard') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.agent_dashboard') }}" :breadcrumb="[___('menus.agent_portal'), ___('label.dashboard')]">

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.my_sales') }}</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($sales) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('permissions.bookings') }}</div><h3 class="mb-0 tv-fw-700">{{ $bookings }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.commission') }}</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($commission) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('menus.wallet') }}</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($wallet) }}</h3></div></div></div>
    </div>

    <div class="tv-card mb-3 mb-md-4"><div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.my_recent_bookings') }}</h4></div></div>

    <x-data-table :headers="[___('label.booking'), ___('label.customer'), ___('label.travel_date'), ___('menus.travelers'), ___('label.amount'), ___('label.status')]">
        @foreach($recent as $b)
            <tr>
                <td><b>BKG-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</b></td>
                <td>{{ $b->customer_name }}</td>
                <td>{{ $b->travel_date?->format('d M Y') }}</td>
                <td>{{ $b->travelers }}</td>
                <td>{{ currency_symbol() }}{{ number_format($b->amount) }}</td>
                <td>{!! $b->statusBadge() !!}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
