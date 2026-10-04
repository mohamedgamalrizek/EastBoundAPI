@extends('frontend.layouts.master')
@section('title', ___('frontend.flight_booking') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.flight_meta_description'))
@section('page', 'flights')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.flight_booking'),
    'subtitle' => ___('frontend.flight_booking_subtitle'),
    'crumbs' => [___('frontend.flight_booking') => null],
])

<section class="section section-space">
    <div class="container">
        {{-- Search/enquiry --}}
        <div class="enquiry-card mb-5" data-aos="fade-up">
            <form action="{{ route('front.flights.live') }}" method="get" class="row g-3 align-items-end" id="liveFlightSearch">
                <script type="application/json" data-flight-fares>@json($flightFares)</script>
                <div class="col-md-3 field">
                    <label class="field-label">{{ ___('frontend.from') }}</label>
                    <select class="form-select select2" name="origin" required>
                        <option value="">{{ ___('frontend.select') }}</option>
                        @foreach($flightCities as $c)
                            @if($c->code)<option value="{{ $c->code }}">{{ $c->city }} ({{ $c->code }})</option>@endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 field">
                    <label class="field-label">{{ ___('frontend.to') }}</label>
                    <select class="form-select select2" name="destination" required>
                        <option value="">{{ ___('frontend.select') }}</option>
                        @foreach($flightCities as $c)
                            @if($c->code)<option value="{{ $c->code }}">{{ $c->city }} ({{ $c->code }})</option>@endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 field"><label class="field-label">{{ ___('frontend.departure') }}</label><input type="date" class="form-control" name="departure_date" min="{{ now()->toDateString() }}" required></div>
                <div class="col-md-2 field"><label class="field-label">{{ ___('frontend.return') }}</label><input type="date" class="form-control" name="return_date"></div>
                <input type="hidden" name="adults" value="1"><input type="hidden" name="currency" value="BDT">
                <div class="col-md-2"><button class="btn btn-brand btn-block"><i class="fa-solid fa-magnifying-glass"></i> {{ ___('frontend.search') }}</button></div>
                <div class="col-12">
                    <small class="text-muted-2" data-flight-fare-hint hidden data-currency="{{ currency_symbol() }}" data-no-fare-text="{{ ___('frontend.no_published_fare') }}" data-fare-estimate-format="{{ ___('frontend.fare_estimate_format') }}"></small>
                </div>
            </form>
            <div id="liveFlightResults" class="mt-4"></div>
        </div>

        {{-- Published fare deals (CMS: Flight Routes) --}}
        @if($routes->isNotEmpty())
        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-route"></i> {{ ___('frontend.popular_routes') }}</span>
            <h2>{{ ___('frontend.top_flight_deals') }}</h2>
        </div>
        <div class="row g-4">
            @foreach($routes as $route)
            <div class="col-md-6 col-lg-4" data-aos="fade-up">
                <div class="card card-hover p-4 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 fw-700">
                            <span>{{ $route->origin }}{{ $route->origin_code ? ' (' . $route->origin_code . ')' : '' }}</span>
                            <i class="fa-solid fa-plane text-muted-2"></i>
                            <span>{{ $route->destination }}{{ $route->destination_code ? ' (' . $route->destination_code . ')' : '' }}</span>
                        </div>
                        <div class="text-muted-2 text-14 mt-1">
                            {{ $route->airline ?: ___('frontend.multiple_airlines') }} · {{ $route->trip_type }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="pc-price price-size-md">{{ currency_symbol() }}{{ number_format($route->fare) }}</div>
                        <a href="{{ route('front.book', ['type' => 'flight', 'from' => $route->origin, 'to' => $route->destination]) }}" class="btn btn-soft btn-sm mt-1">{{ ___('frontend.book') }}</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <i class="fa-solid fa-plane-departure fa-2x text-muted-2 mb-3"></i>
            <p class="text-muted-2 mb-3">{{ ___('frontend.no_fare_deals') }}</p>
            <a href="{{ route('front.book', 'flight') }}" class="btn btn-brand">{{ ___('frontend.request_fare') }}</a>
        </div>
        @endif
    </div>
</section>
@endsection
@push('scripts')
<script>document.getElementById('liveFlightSearch').addEventListener('submit',async function(e){e.preventDefault();const box=document.getElementById('liveFlightResults');box.innerHTML='<p>Searching live fares…</p>';try{const r=await fetch(this.action+'?'+new URLSearchParams(new FormData(this)));const j=await r.json();if(!r.ok)throw new Error(j.message||'Search failed');box.innerHTML=(j.data||[]).map(x=>{const s=x.itineraries?.[0]?.segments?.[0]||{};return `<div class="card p-3 mb-2 d-flex flex-row justify-content-between"><span><b>${s.carrierCode||''}</b> ${s.departure?.iataCode||''} → ${s.arrival?.iataCode||''}</span><b>${x.price?.currency||''} ${x.price?.grandTotal||''}</b></div>`}).join('')||'<p>No flights found.</p>'}catch(err){box.innerHTML=`<div class="alert alert-warning">${err.message}</div>`}});</script>
@endpush
