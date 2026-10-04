@extends('backend.partials.master')
@section('title') Hotel Reports @endsection
@section('maincontent')
<x-page title="Hotel Reports" :breadcrumb="['Hotel','Reports']">
    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Total Hotels</div><h3 class="mb-0">{{ $totalHotels }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Active Hotels</div><h3 class="mb-0 text-success">{{ $activeCount }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Total Rooms</div><h3 class="mb-0">{{ number_format($totalRooms) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Avg Rate/night</div><h3 class="mb-0">{{ currency_symbol() }}{{ number_format($avgPrice) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Room Types</div><h3 class="mb-0">{{ number_format($roomTypes) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Total Bookings</div><h3 class="mb-0 text-info">{{ number_format($totalBookings) }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <h4 class="title-site mb-3">Hotels by City</h4>
            <x-data-table :headers="['City','Hotels']">
                @foreach($byCity as $city => $total)
                    <tr><td><b>{{ $city }}</b></td><td>{{ $total }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-lg-6">
            <h4 class="title-site mb-3">Hotels by Star Rating</h4>
            <x-data-table :headers="['Star Rating','Hotels']">
                @foreach($byCategory as $category => $total)
                    <tr><td><span class="text-warning"><i class="fa fa-star"></i> {{ $category }}</span></td><td>{{ $total }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h4 class="title-site mb-2">Rate by Hotel</h4>
            <x-data-table :headers="['Hotel','City','Stars','Rooms','Rate/night','Status']">
                @foreach($hotels as $h)
                    @php $c = $h->status === 'active' ? 'success' : 'danger'; @endphp
                    <tr>
                        <td><b>{{ $h->name }}</b></td>
                        <td>{{ $h->city }}</td>
                        <td><span class="text-warning"><i class="fa fa-star"></i> {{ $h->category }}</span></td>
                        <td>{{ $h->rooms_count }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($h->price_per_night) }}</td>
                        <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($h->status) }}</span></td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-page>
@endsection
