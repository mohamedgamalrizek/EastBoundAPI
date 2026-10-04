@extends('frontend.layouts.master')
@section('title', $package->title . ' — FLOW')
@section('meta', \Illuminate\Support\Str::limit(strip_tags($package->description ?: 'Book the '.$package->title.' package with FLOW.'), 150))
@section('page', 'packages')

@php
    $img = \Illuminate\Support\Str::startsWith($package->image ?? '', 'http')
        ? $package->image
        : ($package->image ? asset($package->image) : placeholder_image('wide'));
@endphp

@section('content')
@include('frontend.components.page-hero', [
    'title' => $package->title,
    'crumbs' => [___('frontend.tour_packages') => route('front.packages'), $package->destination => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-4">
            {{-- Main --}}
            <div class="col-lg-8">
                <img src="{{ $img }}" class="package-cover rounded-tv shadow-tv mb-4" alt="{{ $package->title }}">

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge-tv is-accent">{{ $package->category }}</span>
                    <span class="badge-tv"><i class="fa-regular fa-clock"></i> {{ $package->duration_days }} {{ ___('label.days') }} / {{ $package->duration_nights }} {{ ___('label.nights') }}</span>
                    <span class="badge-tv"><i class="fa-solid fa-location-dot"></i> {{ $package->destination }}</span>
                    @if($package->category)
                        <span class="badge-tv is-success"><i class="fa-solid fa-tag"></i> {{ $package->category }}</span>
                    @endif
                </div>

                <h2 class="mb-3">{{ ___('frontend.overview') }}</h2>
                <p class="lead">{{ $package->description ?: str_replace(':name', settings('name') ?: 'FLOW', ___('frontend.curated_travel_experience')) }}</p>

                {{-- What's included — per-package, edited in Packages → Inclusions. --}}
                @php $inclusions = $package->inclusionList(); @endphp
                @if($inclusions)
                <h2 class="mt-4 mb-3">{{ ___('frontend.whats_included') }}</h2>
                <div class="row g-3 my-2">
                    @foreach($inclusions as $item)
                    <div class="col-sm-6">
                        <div class="feature-item">
                            <div class="fi-icon"><i class="fa-solid fa-circle-check"></i></div>
                            <div><h5 class="mb-0 text-16">{{ $item }}</h5></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @php $exclusions = $package->exclusionList(); @endphp
                @if($exclusions)
                <h2 class="mt-4 mb-3">{{ ___('frontend.not_included') }}</h2>
                <ul class="text-muted-2">
                    @foreach($exclusions as $item)<li>{{ $item }}</li>@endforeach
                </ul>
                @endif

                {{-- Itinerary — per-package, edited under Packages → Day-by-day itinerary. --}}
                @if($package->itineraries->isNotEmpty())
                <h2 class="mt-4 mb-3">{{ ___('frontend.day_by_day_itinerary') }}</h2>
                <div class="accordion" id="itinerary">
                    @foreach($package->itineraries as $day)
                    <div class="accordion-item border-0 mb-2 card">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} fw-700" type="button" data-bs-toggle="collapse" data-bs-target="#day{{ $day->id }}">
                                {{ ___('frontend.day_word') }} {{ $day->day_number }} — {{ $day->title }}
                            </button>
                        </h2>
                        <div id="day{{ $day->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#itinerary">
                            <div class="accordion-body text-muted-2">
                                {{ $day->description ?: ___('frontend.itinerary_details_fallback') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- The guide leading the next departure, when one is assigned. --}}
                @if($guide)
                <h2 class="mt-4 mb-3">{{ ___('frontend.your_tour_guide') }}</h2>
                <div class="card p-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ media_url($guide->photo, initials_avatar($guide->name)) }}"
                             alt="{{ $guide->name }}" class="rc-avatar rc-avatar-64" width="64" height="64">
                        <div>
                            <h5 class="mb-1">{{ $guide->name }}</h5>
                            <div class="text-muted-2 small">
                                @if($guide->experience_years)
                                    {{ $guide->experience_years }}+ {{ ___('frontend.years_experience') }}
                                @endif
                                @if($guide->languages)
                                    · {{ $guide->languages }}
                                @endif
                            </div>
                            @if($guide->avg_rating !== null)
                            <div class="rc-stars mt-1">
                                @php $stars = (int) round($guide->avg_rating); @endphp
                                {!! str_repeat('<i class="fa-solid fa-star"></i>', $stars) . str_repeat('<i class="fa-regular fa-star"></i>', 5 - $stars) !!}
                                <span class="small text-muted-2 ms-1">{{ $guide->avg_rating }} ({{ $guide->ratings->count() }})</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @if($guide->bio)
                    <p class="text-muted-2 mb-0 mt-3">{{ $guide->bio }}</p>
                    @endif
                </div>
                @endif
            </div>

            {{-- Sticky booking card --}}
            <div class="col-lg-4">
                <div class="card p-4 sticky-header-offset">
                    <div class="text-muted-2 text-14">{{ ___('frontend.starting_from') }}</div>
                    <div class="pc-price price-size-lg">{{ currency_symbol() }}{{ number_format($package->price) }} <small>{{ ___('frontend.per_adult') }}</small></div>
                    @if($package->child_price)
                        <div class="text-muted-2 text-14">{{ currency_symbol() }}{{ number_format($package->child_price) }} {{ ___('frontend.per_child') }}</div>
                    @endif
                    @if($package->single_supplement)
                        <div class="text-muted-2 text-14">+{{ currency_symbol() }}{{ number_format($package->single_supplement) }} {{ ___('frontend.single_supplement_note') }}</div>
                    @endif
                    <div class="mb-3"></div>

                    @if(session('success'))
                        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('front.book.store', $package->id) }}" method="post" class="d-flex flex-column gap-2">
                        @csrf
                        <div>
                            <label class="form-label">{{ ___('frontend.full_name') }}</label>
                            <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" placeholder="{{ ___('frontend.your_name_placeholder') }}">
                            @error('customer_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">{{ ___('frontend.phone') }}</label>
                            <input type="text" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" placeholder="{{ ___('frontend.phone_placeholder') }}">
                            @error('customer_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">{{ ___('frontend.email') }} <span class="text-muted-2">({{ ___('frontend.optional') }})</span></label>
                            <input type="email" name="customer_email" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email') }}" placeholder="{{ ___('frontend.email_example') }}">
                            @error('customer_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-2">
                            <div class="col-7">
                                <label class="form-label">{{ ___('frontend.travel_date') }}</label>
                                <div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" name="travel_date" class="form-control @error('travel_date') is-invalid @enderror" value="{{ old('travel_date') }}" placeholder="{{ ___('frontend.select_date') }}"></div>
                                @error('travel_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-5">
                                <label class="form-label">{{ ___('frontend.travelers') }}</label>
                                <input type="number" name="travelers" id="bookTravelers" min="1" max="50" class="form-control @error('travelers') is-invalid @enderror" value="{{ old('travelers', 2) }}"
                                       data-unit-price="{{ (float) $package->price }}">
                                @error('travelers')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="form-label">{{ ___('frontend.promo_code') }} <span class="text-muted-2">({{ ___('frontend.optional') }})</span></label>
                            <input type="text" name="coupon_code" class="form-control @error('coupon_code') is-invalid @enderror"
                                   value="{{ old('coupon_code') }}" placeholder="{{ ___('frontend.promo_code_placeholder') }}" autocomplete="off">
                            @error('coupon_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Bookings bill price × travellers, so the figure is shown
                             before submitting rather than on the confirmation call.
                             A promo code is applied by the server on submit, so the
                             total here is the price before any discount. --}}
                        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-1">
                            <span class="text-muted-2 text-14" id="bookTotalLabel"></span>
                            <b class="price-size-md" id="bookTotal">{{ currency_symbol() }}{{ number_format($package->price * 2) }}</b>
                        </div>

                        {{-- Visa is excluded from package pricing, so the intent has to
                             be captured here or it surfaces days before departure. --}}
                        <label class="d-flex align-items-start gap-2 text-14 mt-1">
                            <input type="checkbox" name="needs_visa" value="1" class="mt-1" @checked(old('needs_visa'))>
                            <span>{{ ___('frontend.i_need_visa_help') }}</span>
                        </label>

                        <button type="submit" class="btn btn-brand btn-block mt-2">{{ ___('frontend.request_booking') }}</button>
                    </form>
                    <a href="{{ route('front.contact') }}" class="btn btn-soft btn-block mt-2"><i class="fa-solid fa-headset"></i> {{ ___('frontend.ask_a_question') }}</a>
                    <div class="d-flex align-items-center gap-2 mt-3 text-muted-2 text-14">
                        <i class="fa-solid fa-shield-halved text-success-tv"></i> {{ ___('frontend.no_payment_required') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Traveller reviews. Every one of these is attached to a booking
             that was paid for and travelled on, which is what the badge says. --}}
        @if($package->approvedReviews->isNotEmpty())
        <div class="mt-5 pt-4" id="reviews">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <h2 class="mb-0">{{ ___('frontend.traveller_reviews') }}</h2>
                <div class="d-flex align-items-center gap-2">
                    @php $stars = (int) round($package->avg_rating); @endphp
                    <span class="rc-stars">
                        {!! str_repeat('<i class="fa-solid fa-star"></i>', $stars) . str_repeat('<i class="fa-regular fa-star"></i>', 5 - $stars) !!}
                    </span>
                    <b>{{ $package->avg_rating }}</b>
                    <span class="text-muted-2 text-14">({{ $package->reviews_count }})</span>
                </div>
            </div>

            <div class="row g-4">
                @foreach($package->approvedReviews as $review)
                    <div class="col-md-6">
                        <div class="card p-4 h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div>
                                    <b>{{ $review->authorName() }}</b>
                                    @if($review->isVerified())
                                        <span class="text-success-tv text-14 ms-1" title="{{ ___('frontend.verified_booking_hint') }}">
                                            <i class="fa-solid fa-circle-check"></i> {{ ___('frontend.verified_booking') }}
                                        </span>
                                    @endif
                                </div>
                                <span class="rc-stars text-14">
                                    {!! str_repeat('<i class="fa-solid fa-star"></i>', $review->rating) . str_repeat('<i class="fa-regular fa-star"></i>', 5 - $review->rating) !!}
                                </span>
                            </div>
                            @if($review->title)<h6 class="mt-3 mb-1">{{ $review->title }}</h6>@endif
                            @if($review->comment)<p class="text-muted-2 mb-0 {{ $review->title ? '' : 'mt-3' }}">{{ $review->comment }}</p>@endif
                            @if($review->reply)
                                <div class="border-start ps-3 mt-3">
                                    <div class="text-14 text-muted-2"><b>{{ settings('site_name') ?: ___('frontend.our_reply') }}</b></div>
                                    <p class="text-muted-2 text-14 mb-0">{{ $review->reply }}</p>
                                </div>
                            @endif
                            <div class="text-muted-2 text-14 mt-3">{{ $review->created_at->format('d M Y') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Related --}}
        @if(isset($related) && $related->count())
        <div class="mt-5 pt-4">
            <h2 class="mb-4">{{ ___('frontend.you_may_also_like') }}</h2>
            <div class="row g-4">
                @foreach($related as $rel)
                    <div class="col-sm-6 col-lg-3">@include('frontend.components.package-card', ['package' => $rel])</div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

