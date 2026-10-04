@extends('frontend.layouts.master')
@section('title', ___('frontend.transport_booking') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.transport_meta_description'))
@section('page', 'transport')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.transport_booking'),
    'subtitle' => ___('frontend.transport_booking_subtitle'),
    'crumbs' => [___('frontend.transport_booking') => null],
])

<section class="section section-space">
    <div class="container">
        {{-- Transport products (CMS: Transport Services) --}}
        <div class="row g-4">
            @forelse($services as $i => $service)
            @php
                // Prefer the cheapest fare actually booked for this vehicle
                // type; fall back to the configured price, then to a quote.
                $from = $service->booked_from ?: ($service->price_from > 0 ? $service->price_from : null);
                $unit = $service->booked_from ? '' : $service->price_unit;
            @endphp
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $i * 70 }}">
                <div class="service-card h-100">
                    <div class="sc-icon"><i class="fa-solid {{ $service->icon ?: 'fa-van-shuttle' }}"></i></div>
                    <h4 class="text-20">{{ $service->title }}</h4>
                    <p>{{ $service->description }}</p>
                    <div class="pc-price my-2 price-size-sm">
                        @if($from)
                            {{ ___('frontend.from') }} {{ currency_symbol() }}{{ number_format($from) }}{{ $unit }}
                        @else
                            {{ ___('frontend.request_a_quote') }}
                        @endif
                    </div>
                    <a href="{{ route('front.book', ['type' => $service->booking_type, 'service' => $service->title]) }}" class="btn btn-soft btn-sm btn-block">{{ ___('frontend.book_now') }}</a>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-van-shuttle fa-2x text-muted-2 mb-3"></i>
                <p class="text-muted-2 mb-0">{{ ___('frontend.no_transport_services') }}</p>
            </div>
            @endforelse
        </div>

        <div class="enquiry-card mt-5" data-aos="fade-up">
            <h4 class="mb-3">{{ ___('frontend.quick_transport_enquiry') }}</h4>
            <form action="{{ route('front.book', 'airport-pickup') }}" method="get" class="row g-3 align-items-end">
                <div class="col-md-3 field"><label class="field-label">{{ ___('frontend.pickup') }}</label><div class="input-icon"><i class="fa-solid fa-location-dot"></i><input class="form-control" name="pickup" placeholder="{{ ___('frontend.pickup_location') }}"></div></div>
                <div class="col-md-3 field"><label class="field-label">{{ ___('frontend.drop_off') }}</label><div class="input-icon"><i class="fa-solid fa-flag-checkered"></i><input class="form-control" name="dropoff" placeholder="{{ ___('frontend.destination') }}"></div></div>
                <div class="col-md-2 field"><label class="field-label">{{ ___('frontend.date') }}</label><div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control" name="date" placeholder="{{ ___('frontend.select_date') }}"></div></div>
                <div class="col-md-2 field"><label class="field-label">{{ ___('frontend.vehicle') }}</label><select class="form-select" name="vehicle">@foreach($vehicleCategories as $vc)<option value="{{ $vc }}">{{ $vc }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-brand btn-block">{{ ___('frontend.enquire') }}</button></div>
            </form>
        </div>
    </div>
</section>
@endsection

