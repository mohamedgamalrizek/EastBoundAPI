@extends('frontend.layouts.master')
@section('title', ___('frontend.hotel_booking') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.hotel_meta_description'))
@section('page', 'hotels')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.hotel_booking'),
    'subtitle' => ___('frontend.hotel_booking_subtitle'),
    'crumbs' => [___('frontend.hotel_booking') => null],
])

<section class="section section-space">
    <div class="container">
        {{-- Searches our real hotel inventory; the enquiry form handles the booking. --}}
        <div class="enquiry-card mb-5" data-aos="fade-up">
            <form action="{{ route('front.hotels') }}" method="get" class="row g-3 align-items-end">
                <div class="col-md-5 field">
                    <label class="field-label">{{ ___('frontend.destination') }}</label>
                    <div class="input-icon"><i class="fa-solid fa-location-dot"></i>
                        <input class="form-control" name="city" list="hotel-cities" value="{{ request('city') }}" placeholder="{{ ___('frontend.city_country_hotel_placeholder') }}">
                    </div>
                    <datalist id="hotel-cities">
                        @foreach($cities as $city)<option value="{{ $city }}">@endforeach
                    </datalist>
                </div>
                <div class="col-md-3 field">
                    <label class="field-label">{{ ___('frontend.star_rating') }}</label>
                    <select class="form-select" name="stars">
                        <option value="">{{ ___('frontend.any_rating') }}</option>
                        @for($s = 5; $s >= 1; $s--)
                            <option value="{{ $s }}" @selected(request('stars') == $s)>{{ $s }} {{ ___('frontend.star') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-brand btn-block"><i class="fa-solid fa-magnifying-glass"></i> {{ ___('frontend.search') }}</button></div>
                <div class="col-md-2"><a href="{{ route('front.book', 'hotel') }}" class="btn btn-outline-brand btn-block">{{ ___('frontend.enquire') }}</a></div>
            </form>
        </div>

        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-hotel"></i> {{ ___('frontend.featured_stays') }}</span>
            <h2>{{ request('city') ? str_replace(':query', request('city'), ___('frontend.hotels_matching')) : ___('frontend.popular_hotels_resorts') }}</h2>
        </div>

        <div class="row g-4">
            @forelse($hotels as $hotel)
            @php
                $img = media_url($hotel->image, placeholder_image('card'));
                $stars = max(0, min(5, (int) $hotel->category));
            @endphp
            <div class="col-md-6 col-lg-4" data-aos="fade-up">
                <article class="card package-card card-hover h-100">
                    <div class="pc-media">
                        <img src="{{ $img }}" alt="{{ $hotel->name }}" loading="lazy">
                        @if($stars)
                        <span class="badge-tv is-accent pc-badge">@for($s = 0; $s < $stars; $s++)<i class="fa-solid fa-star text-13"></i>@endfor</span>
                        @endif
                    </div>
                    <div class="pc-body">
                        <div class="pc-meta"><span><i class="fa-solid fa-location-dot"></i> {{ $hotel->city }}{{ $hotel->country ? ', ' . $hotel->country : '' }}</span></div>
                        <h3 class="pc-title">{{ $hotel->name }}</h3>
                        @if($hotel->description)
                            <p class="text-muted-2 text-14 mb-2">{{ Str::limit($hotel->description, 90) }}</p>
                        @endif
                        <div class="pc-foot">
                            <div class="pc-price">{{ currency_symbol() }}{{ number_format($hotel->price_per_night) }} <small>{{ ___('frontend.per_night_suffix') }}</small></div>
                            <a href="{{ route('front.book', ['type' => 'hotel', 'hotel' => $hotel->name, 'city' => $hotel->city]) }}" class="btn btn-soft btn-sm">{{ ___('frontend.book') }}</a>
                        </div>
                    </div>
                </article>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-hotel fa-2x text-muted-2 mb-3"></i>
                <p class="text-muted-2 mb-3">
                    {{ request('city') || request('stars') ? ___('frontend.no_hotels_matched') : ___('frontend.no_hotels_published') }}
                </p>
                <a href="{{ route('front.book', 'hotel') }}" class="btn btn-brand">{{ ___('frontend.tell_us_where_stay') }}</a>
            </div>
            @endforelse
        </div>
    </div>
</section>
@endsection

