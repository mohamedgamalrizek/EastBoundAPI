@extends('backend.partials.master')
@section('title') Booking Details @endsection
@section('maincontent')
<x-page title="Booking Details" :breadcrumb="['Flight','Booking Details']">

    @if($booking)
        @php $c = in_array($booking->status, ['Confirmed','Reissued']) ? 'success' : ($booking->status === 'Pending' ? 'warning' : 'danger'); @endphp
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="tv-card">
                    <div class="tv-card-head">
                        <h4 class="title-site mb-0">{{ $booking->airline }} · {{ $booking->route }}</h4>
                    </div>
                    <div class="tv-card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted">PNR</small>
                                <div class="h5">{{ $booking->pnr }}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Passenger</small>
                                <div class="h5">{{ $booking->passenger_name }}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Flight Date</small>
                                <div class="h5">{{ $booking->flight_date?->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Ticket No</small>
                                <div class="h5">{{ $booking->ticket_no ?? '—' }}</div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Airline</small>
                                <div class="h5">{{ $booking->airline }}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Route</small>
                                <div class="h5">{{ $booking->route }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="tv-card">
                    <div class="tv-card-body">
                        <h6>Fare Summary</h6>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            <span><span class="bullet-badge bullet-badge-{{ $c }}">{{ $booking->status }}</span></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-700">
                            <b>Total Fare</b>
                            <b>{{ currency_symbol() }}{{ number_format($booking->fare) }}</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="tv-card"><div class="tv-card-body text-center text-muted">No booking found.</div></div>
    @endif

</x-page>
@endsection
