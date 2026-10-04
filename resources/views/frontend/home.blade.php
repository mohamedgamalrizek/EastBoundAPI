@extends('frontend.layouts.master')
@section('title', (settings('name') ?: 'FLOW') . ' - ' . ___('frontend.home_meta_title'))
@section('meta', ___('frontend.home_meta_description'))
@section('page', 'home')

@section('content')
@php
    $rt = fn ($name, ...$a) => \Illuminate\Support\Facades\Route::has($name) ? route($name, ...$a) : '#';
    // First active slider drives the hero; the fallback keeps the page whole
    // before any slider has been configured in the CMS.
    $hero = $slides->first();
@endphp

{{-- ============ 1. HERO + SEARCH ============ --}}
{{-- The stylesheet ships a default hero photo; a CMS slider image overrides
     just the photo layer while keeping the brand overlay gradients. --}}
<section class="hero"
    @if($hero?->image)
        style="background-image: var(--tv-gradient-hero-overlay), url('{{ media_url($hero->image) }}'), var(--tv-gradient-deep); background-size: cover; background-position: center; background-repeat: no-repeat;"
    @endif
>
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-9" data-aos="fade-up">
                <span class="badge-tv is-accent mb-3">
                    <i class="fa-solid fa-star"></i> {{ $hero->badge ?? ___('frontend.hero_badge') }}
                </span>
                <h1 class="display-5 fw-800">{!! $hero?->title ? e($hero->title) : ___('frontend.hero_title') !!}</h1>
                <p class="hero-sub">{{ $hero->subtitle ?? ___('frontend.hero_subtitle') }}</p>
                @if($hero?->cta_text)
                    <a href="{{ $hero->cta_link ?: route('front.packages') }}" class="btn btn-brand btn-lg mb-3">{{ $hero->cta_text }}</a>
                @endif
                <div class="hero-trust">
                    <span class="ht-item"><i class="fa-solid fa-circle-check"></i> {{ $stats[2]['num'] }}{{ $stats[2]['suffix'] }} {{ ___('frontend.visa_success') }}</span>
                    <span class="ht-item"><i class="fa-solid fa-circle-check"></i> {{ ___('frontend.iata_accredited') }}</span>
                    <span class="ht-item"><i class="fa-solid fa-circle-check"></i> {{ ___('frontend.support_24_7') }}</span>
                </div>
            </div>
        </div>

        {{-- Multi-tab search widget. Tours and Hotels search real inventory;
             Flights and Visa open the enquiry form pre-filled. --}}
        <div class="search-widget mt-4" data-aos="fade-up" data-aos-delay="100">
            <div class="search-tabs">
                <button class="stab active flex-grow-1 flex-sm-grow-0 gap-1_5 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2_5 text-12 sm:text-14" data-target="tab-flight"><i class="fa-solid fa-plane"></i> {{ ___('frontend.flights') }}</button>
                <button class="stab flex-grow-1 flex-sm-grow-0 gap-1_5 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2_5 text-12 sm:text-14" data-target="tab-hotel"><i class="fa-solid fa-hotel"></i> {{ ___('frontend.hotels') }}</button>
                <button class="stab flex-grow-1 flex-sm-grow-0 gap-1_5 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2_5 text-12 sm:text-14" data-target="tab-tour"><i class="fa-solid fa-umbrella-beach"></i> {{ ___('frontend.tours') }}</button>
                <button class="stab flex-grow-1 flex-sm-grow-0 gap-1_5 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2_5 text-12 sm:text-14" data-target="tab-visa"><i class="fa-solid fa-passport"></i> {{ ___('frontend.visa') }}</button>
            </div>

            {{-- Flights --}}
            <div class="search-panel active" id="tab-flight">
                <form action="{{ route('front.book', 'flight') }}" method="get" class="search-row" data-flight-fare-form>
                    <script type="application/json" data-flight-fares>@json($flightFares)</script>
                    <div class="field">
                        <label class="field-label">{{ ___('frontend.from') }}</label>
                        <select class="form-select select2" name="from">
                            <option value="">{{ ___('frontend.select') }}</option>
                            @foreach($flightCities as $c)
                                <option value="{{ $c->city }}">{{ $c->city }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="field-label">{{ ___('frontend.to') }}</label>
                        <select class="form-select select2" name="to">
                            <option value="">{{ ___('frontend.select') }}</option>
                            @foreach($flightCities as $c)
                                <option value="{{ $c->city }}">{{ $c->city }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label class="field-label">{{ ___('frontend.departure') }}</label><div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control" name="depart" placeholder="{{ ___('frontend.select_date') }}"></div></div>
                    <div class="field"><label class="field-label">{{ ___('frontend.travelers') }}</label><select class="form-select" name="pax"><option>{{ ___('frontend.one_adult') }}</option><option>{{ ___('frontend.two_adults') }}</option><option>{{ ___('frontend.family_4') }}</option></select></div>
                    <button class="btn btn-brand"><i class="fa-solid fa-magnifying-glass"></i> {{ ___('frontend.search') }}</button>
                    <div class="col-12">
                        <small class="text-muted-2" data-flight-fare-hint hidden data-currency="{{ currency_symbol() }}" data-no-fare-text="{{ ___('frontend.no_published_fare') }}"></small>
                    </div>
                </form>
            </div>
            {{-- Hotels — searches the real hotel inventory --}}
            <div class="search-panel" id="tab-hotel">
                <form action="{{ route('front.hotels') }}" method="get" class="search-row">
                    <div class="field">
                        <label class="field-label">{{ ___('frontend.destination') }}</label>
                        <div class="input-icon"><i class="fa-solid fa-location-dot"></i>
                            <input class="form-control" name="city" list="home-hotel-cities" placeholder="{{ ___('frontend.city_or_hotel_name') }}">
                        </div>
                        <datalist id="home-hotel-cities">@foreach($hotelCities as $city)<option value="{{ $city }}">@endforeach</datalist>
                    </div>
                    <div class="field"><label class="field-label">{{ ___('frontend.check_in') }}</label><div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control" name="checkin" placeholder="{{ ___('frontend.select_date') }}"></div></div>
                    <div class="field"><label class="field-label">{{ ___('frontend.check_out') }}</label><div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control" name="checkout" placeholder="{{ ___('frontend.select_date') }}"></div></div>
                    <div class="field"><label class="field-label">{{ ___('frontend.star_rating') }}</label>
                        <select class="form-select" name="stars">
                            <option value="">{{ ___('frontend.any') }}</option>
                            @for($s = 5; $s >= 3; $s--)<option value="{{ $s }}">{{ $s }} star</option>@endfor
                        </select>
                    </div>
                    <button class="btn btn-brand"><i class="fa-solid fa-magnifying-glass"></i> {{ ___('frontend.search') }}</button>
                </form>
            </div>
            {{-- Tours — searches the real package catalogue --}}
            <div class="search-panel" id="tab-tour">
                <form action="{{ route('front.packages') }}" method="get" class="search-row">
                    <div class="field"><label class="field-label">{{ ___('frontend.destination') }}</label><div class="input-icon"><i class="fa-solid fa-earth-asia"></i><input class="form-control" name="q" placeholder="{{ ___('frontend.maldives_bali') }}"></div></div>
                    <div class="field"><label class="field-label">{{ ___('frontend.category') }}</label>
                        <select class="form-select" name="category">
                            <option value="">{{ ___('frontend.any') }}</option>
                            @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field"><label class="field-label">{{ ___('frontend.sort_by') }}</label>
                        <select class="form-select" name="sort">
                            <option value="">{{ ___('frontend.newest') }}</option>
                            <option value="price_low">{{ ___('frontend.price_low_to_high') }}</option>
                            <option value="price_high">{{ ___('frontend.price_high_to_low') }}</option>
                        </select>
                    </div>
                    <button class="btn btn-brand"><i class="fa-solid fa-magnifying-glass"></i> {{ ___('frontend.search') }}</button>
                </form>
            </div>
            {{-- Visa --}}
            <div class="search-panel" id="tab-visa">
                <form action="{{ route('front.visa') }}" method="get" class="search-row">
                    <div class="field">
                        <label class="field-label">{{ ___('frontend.country') }}</label>
                        <select class="form-select select2" name="q">
                            <option value="">{{ ___('frontend.any') }}</option>
                            @foreach($visaCountries as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label class="field-label">{{ ___('frontend.visa_type') }}</label><select class="form-select" name="type"><option value="">{{ ___('frontend.any') }}</option><option>{{ ___('frontend.tourist') }}</option><option>{{ ___('frontend.business') }}</option><option>{{ ___('frontend.student') }}</option><option>{{ ___('frontend.work') }}</option></select></div>
                    <button class="btn btn-brand"><i class="fa-solid fa-magnifying-glass"></i> {{ ___('frontend.find_visas') }}</button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ============ 1b. PROMO SLIDERS — the sliders the hero isn't using ============ --}}
@php $promoSlides = $slides->skip(1)->values(); @endphp
@if($promoSlides->isNotEmpty())
<section class="section section-space pb-0">
    <div class="container">
        <div class="swiper promo-swiper" data-swiper='{"slidesPerView":1,"spaceBetween":16,"loop":true,"autoplay":{"delay":4500},"breakpoints":{"768":{"slidesPerView":2}}}'>
            <div class="swiper-wrapper">
                @foreach($promoSlides as $slide)
                <div class="swiper-slide h-auto">
                    <a href="{{ $slide->cta_link ?: route('front.packages') }}" class="promo-slide position-relative rounded-4 overflow-hidden d-block">
                        <img src="{{ media_url($slide->image, placeholder_image('slide')) }}"
                             alt="{{ $slide->title }}" loading="lazy" class="w-100 promo-slide-img">
                        <div class="promo-slide-body position-absolute bottom-0 start-0 w-100 p-4 promo-slide-gradient">
                            <h3 class="text-white mb-1">{{ $slide->title }}</h3>
                            @if($slide->subtitle)
                                <p class="text-white-50 mb-0">{{ $slide->subtitle }}</p>
                            @endif
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination mt-4 position-static"></div>
        </div>
    </div>
</section>
@endif

{{-- ============ 2. POPULAR DESTINATIONS (derived from the live catalogue) ============ --}}
@if($destinations->isNotEmpty())
<section class="section section-space">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-compass"></i> {{ ___('frontend.explore') }}</span>
            <h2>{{ ___('frontend.popular_destinations') }}</h2>
            <p>{{ ___('frontend.trending_destinations') }}</p>
        </div>
        <div class="destinations-grid">
            @foreach($destinations as $i => $dest)
            <a href="{{ route('front.packages', ['q' => $dest->name]) }}" class="destination-card" data-aos="zoom-in" data-aos-delay="{{ $i * 80 }}">
                <img src="{{ media_url($dest->image, placeholder_image('card')) }}" alt="{{ $dest->name }}" loading="lazy">
                <div class="dc-body">
                    <h3 class="dc-title">{{ $dest->name }}</h3>
                    <div class="dc-meta">
                        <i class="fa-solid fa-location-dot"></i>
                        {{ $dest->category ? $dest->category . ' · ' : '' }}{{ $dest->tours }} {{ Str::plural('tour', $dest->tours) }}
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ 3. FEATURED PACKAGES ============ --}}
<section class="section section-space section--tint">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div class="section-head text-start m-0">
                <span class="eyebrow"><i class="fa-solid fa-fire"></i> {{ ___('frontend.hand_picked') }}</span>
                <h2>{{ ___('frontend.featured_tour_packages') }}</h2>
            </div>
            <a href="{{ route('front.packages') }}" class="btn btn-outline-brand">{{ ___('frontend.view_all_packages') }} <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="row g-3 g-md-4">
            @forelse($packages as $package)
                <div class="col-6 col-md-6 col-lg-4" data-aos="fade-up">
                    @include('frontend.components.package-card', ['package' => $package])
                </div>
            @empty
                <div class="col-12 text-center text-muted-2 py-5">{{ ___('frontend.no_packages_available') }}</div>
            @endforelse
        </div>
    </div>
</section>

{{-- ============ 4–7. SERVICES — the fixed service menu this site offers ============ --}}
<section class="section section-space">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-briefcase"></i> {{ ___('frontend.what_we_do') }}</span>
            <h2>{{ ___('frontend.everything_for_journey') }}</h2>
            <p>{{ ___('frontend.journey_partner_copy') }}</p>
        </div>
        <div class="row g-4">
            @foreach($services as $i => $service)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 80 }}">
                <a href="{{ $service->href() ?? '#' }}" class="service-card d-block">
                    <div class="sc-icon"><i class="fa-solid {{ $service->icon ?: 'fa-star' }}"></i></div>
                    <h4>{{ $service->title }}</h4>
                    <p>{{ $service->body }}</p>
                    <span class="d-inline-flex align-items-center gap-2 mt-3 fw-700 text-14 link-primary-dark">{{ ___('frontend.learn_more') }} <i class="fa-solid fa-arrow-right"></i></span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ 8. WHY CHOOSE US ============ --}}
<section class="section section-space section--tint">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 why-media" data-aos="fade-right">
                {{-- The section image belongs to the first "why us" block, so an
                     agency can change it from CMS → Content Blocks instead of
                     editing this template. Falls back to the bundled placeholder
                     when nothing is set. --}}
                <img src="{{ media_url(optional($whyUs->first())->image, placeholder_image('slide')) }}" alt="{{ ___('frontend.happy_travelers_alt') }}" loading="lazy">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="eyebrow"><i class="fa-solid fa-shield-heart"></i> {{ ___('frontend.why') }} {{ settings('name') ?: 'FLOW' }}</span>
                <h2 class="mt-2 mb-4">{{ ___('frontend.travel_made_effortless') }}</h2>
                <div class="d-flex flex-column gap-4">
                    @foreach($whyUs as $why)
                    <div class="feature-item">
                        <div class="fi-icon"><i class="fa-solid {{ $why->icon ?: 'fa-circle-check' }}"></i></div>
                        <div><h5>{{ $why->title }}</h5><p>{{ $why->body }}</p></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ 9. TRAVEL STATISTICS ============ --}}
<section class="section section-space">
    <div class="container">
        <div class="stats-band" data-aos="fade-up">
            <div class="row g-4 text-center">
                @foreach($stats as $s)
                <div class="col-6 col-lg-3">
                    <div class="stat">
                        <div class="stat-num"><span data-count="{{ $s['num'] }}" data-suffix="{{ $s['suffix'] }}">0</span></div>
                        <div class="stat-label">{{ $s['label'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============ 10. CUSTOMER REVIEWS ============ --}}
@if($reviews->isNotEmpty())
<section class="section section-space section--tint">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-quote-right"></i> {{ ___('frontend.testimonials') }}</span>
            <h2>{{ ___('frontend.loved_by_travelers') }}</h2>
            <p>{{ ___('frontend.real_stories') }}</p>
        </div>
        <div class="swiper reviews-swiper" data-swiper='{"slidesPerView":1,"breakpoints":{"768":{"slidesPerView":2},"992":{"slidesPerView":3}}}'>
            <div class="swiper-wrapper">
                @foreach($reviews as $review)
                <div class="swiper-slide h-auto">
                    @include('frontend.components.review-card', ['review' => $review])
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination mt-4 position-static"></div>
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('front.testimonials') }}" class="btn btn-outline-brand">{{ ___('frontend.read_all_reviews') }} <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>
@endif

{{-- ============ 11. PARTNER AIRLINES (airlines we publish fares for) ============ --}}
@if($airlines->isNotEmpty())
<section class="section section-space pt-6 md:pt-8 pb-4">
    <div class="container">
        <p class="text-center text-muted-2 eyebrow-text mb-4">{{ ___('frontend.trusted_by_partners') }}</p>
        {{-- Centred flex strip, not a 6-column grid: a trailing row of 2 used to
             hang off to the left, and long names wrapped onto a second line so
             the marks never lined up. --}}
        <div class="partner-strip">
            @foreach($airlines as $airline)
            <div class="partner-logo" title="{{ $airline->name }}">
                @if($airline->logo)
                    <img src="{{ $airline->logo }}" alt="{{ $airline->name }}" loading="lazy">
                @else
                    <i class="fa-solid fa-plane-up"></i><span>{{ $airline->name }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ 12. TRAVEL BLOG ============ --}}
@if($posts->isNotEmpty())
<section class="section section-space section--tint">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div class="section-head text-start m-0">
                <span class="eyebrow"><i class="fa-solid fa-newspaper"></i> {{ ___('frontend.travel_blog') }}</span>
                <h2>{{ ___('frontend.tips_travel_inspiration') }}</h2>
            </div>
            <a href="{{ route('front.blog') }}" class="btn btn-outline-brand">{{ ___('frontend.all_articles') }} <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            @foreach($posts as $i => $post)
            @php $cover = media_url($post->image, placeholder_image('card')); @endphp
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                <article class="card blog-card card-hover h-100">
                    <a href="{{ route('front.blog.show', $post->slug) }}" class="bc-media"><img src="{{ $cover }}" alt="{{ $post->title }}" loading="lazy"></a>
                    <div class="bc-body">
                        <div class="bc-meta">
                            @if($post->category)<span class="badge-tv">{{ $post->category }}</span>@endif
                            <i class="fa-regular fa-calendar ms-2"></i> {{ dateFormat($post->published_at ?: $post->created_at) }}
                        </div>
                        <h3 class="bc-title"><a href="{{ route('front.blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                        <a href="{{ route('front.blog.show', $post->slug) }}" class="fw-700 text-14 link-primary-dark">{{ ___('frontend.read_more') }} <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ 13. NEWSLETTER — records a real CRM lead ============ --}}
<section class="section section-space">
    <div class="container">
        <div class="newsletter" data-aos="zoom-in">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="eyebrow"><i class="fa-solid fa-envelope-open-text"></i> {{ ___('frontend.stay_in_loop') }}</span>
                    <h2 class="mt-2 mb-2">{{ ___('frontend.exclusive_deals') }}</h2>
                    <p class="text-muted-2 mb-0">{{ ___('frontend.newsletter_copy') }}</p>
                </div>
                <div class="col-lg-6">
                    <form id="newsletter" class="newsletter-form ms-lg-auto" data-newsletter-form
                          data-loading-text="{{ ___('frontend.subscribing') }}"
                          data-generic-error="{{ ___('frontend.invalid_email_error') }}"
                          data-success-text="{{ ___('frontend.newsletter_success') }}"
                          data-fail-text="{{ ___('frontend.newsletter_fail') }}"
                          action="{{ route('front.newsletter') }}" method="post">
                        @csrf
                        <input type="hidden" name="source" value="home">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="{{ ___('frontend.email_placeholder') }}" required>
                        <button type="submit" class="btn btn-brand">{{ ___('frontend.subscribe') }}</button>
                    </form>
                    <p class="mt-2 mb-0 ms-lg-auto text-14 {{ session('success') ? 'text-success-tv' : ($errors->has('email') ? 'text-danger' : 'd-none') }}" data-newsletter-message>
                        {{ session('success') ?: $errors->first('email') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ 14. CALL TO ACTION ============ --}}
<section class="section section-space pt-0">
    <div class="container">
        <div class="cta-band" data-aos="fade-up">
            <span class="badge-tv is-accent mb-3"><i class="fa-solid fa-plane-departure"></i> {{ ___('frontend.ready_when_you_are') }}</span>
            <h2 class="mb-2">{{ ___('frontend.plan_next_adventure') }}</h2>
            <p class="mb-4 mx-auto home-cta-copy">{{ ___('frontend.plan_next_copy') }}</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="{{ route('front.packages') }}" class="btn btn-white btn-lg">{{ ___('frontend.browse_packages') }}</a>
                <a href="{{ route('front.contact') }}" class="btn btn-white btn-lg">{{ ___('frontend.talk_to_expert') }}</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('frontend/js/newsletter.js') }}?v={{ filemtime(public_path('frontend/js/newsletter.js')) }}"></script>
@endpush

