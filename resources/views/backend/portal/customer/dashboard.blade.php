@extends('backend.partials.master')
@section('title') {{ ___('label.my_dashboard') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.my_dashboard') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.dashboard')]">

    <div class="row">
        <div class="col-md-3 col-6 "><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('menus.my_bookings') }}</div><h3 class="mb-0 tv-fw-700">{{ $bookingCount }}</h3></div></div></div>
        <div class="col-md-3 col-6 "><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.upcoming_trips') }}</div><h3 class="mb-0 text-success tv-fw-700">{{ $upcomingCount }}</h3></div></div></div>
        <div class="col-md-3 col-6 "><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('menus.wallet') }}</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($walletBalance) }}</h3></div></div></div>
        <div class="col-md-3 col-6 "><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('menus.visa_applications') }}</div><h3 class="mb-0 tv-fw-700">{{ $visaCount }}</h3></div></div></div>
    </div>

    <div class="tv-card">
        <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.recent_bookings') }}</h4></div>
        <div class="tv-card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="bg"><tr><th>{{ ___('label.booking') }}</th><th>{{ ___('label.detail') }}</th><th>{{ ___('label.travel_date') }}</th><th>{{ ___('label.amount') }}</th><th>{{ ___('label.status') }}</th></tr></thead>
                    <tbody>
                        @foreach($recent as $b)
                            @php
                                $map = ['pending'=>'warning','confirmed'=>'info','paid'=>'success','cancelled'=>'danger'];
                                $c = $map[$b->status] ?? 'warning';
                            @endphp
                            <tr>
                                <td><b>BKG-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</b></td>
                                <td>{{ $b->customer_name }}</td>
                                <td>{{ $b->travel_date?->format('d M Y') }}</td>
                                <td>{{ currency_symbol() }}{{ number_format($b->amount) }}</td>
                                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($b->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-page>
@endsection
