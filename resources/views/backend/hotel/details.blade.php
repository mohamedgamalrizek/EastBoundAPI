@extends('backend.partials.master')
@section('title') Hotel Details @endsection
@section('maincontent')
<x-page title="Hotel Details" :breadcrumb="['Hotel','Hotels','Details']">
    @if($hotel)
    @php $c = $hotel->status === 'active' ? 'success' : 'danger'; @endphp
    <div class="row">
        <div class="col-lg-5 mb-4"><div class="tv-card h-100">
            <div class="tv-card-body">
                <div class="d-flex justify-content-between"><h4 class="mb-0">{{ $hotel->name }}</h4><span class="text-warning"><i class="fa fa-star"></i> {{ $hotel->category }}</span></div>
                <div class="text-muted mb-3"><i class="fa fa-location-dot mr-1"></i>{{ $hotel->city }}, {{ $hotel->country }}</div>
                <div><span class="bullet-badge bullet-badge-info">{{ $hotel->category }}-Star</span> <span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($hotel->status) }}</span></div>
            </div>
        </div></div>
        <div class="col-lg-7 mb-4"><div class="tv-card h-100"><div class="tv-card-head"><h4 class="title-site mb-0">Overview</h4></div>
            <div class="tv-card-body"><div class="table-responsive"><table class="table table-responsive-sm mb-0">
                <tbody>
                    <tr><th class="tv-w-40p">Hotel Name</th><td>{{ $hotel->name }}</td></tr>
                    <tr><th>City</th><td>{{ $hotel->city }}</td></tr>
                    <tr><th>Country</th><td>{{ $hotel->country }}</td></tr>
                    <tr><th>Star Rating</th><td>{{ $hotel->category }} Star</td></tr>
                    <tr><th>Total Rooms</th><td>{{ $hotel->rooms_count }}</td></tr>
                    <tr><th>Rate / night</th><td>{{ currency_symbol() }}{{ number_format($hotel->price_per_night) }}</td></tr>
                    <tr><th>Status</th><td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($hotel->status) }}</span></td></tr>
                </tbody>
            </table></div></div>
        </div></div>
    </div>
    @else
        <div class="tv-card"><div class="tv-card-body text-center text-muted">No hotel found.</div></div>
    @endif
</x-page>
@endsection
