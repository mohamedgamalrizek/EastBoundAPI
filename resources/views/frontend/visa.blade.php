@extends('frontend.layouts.master')
@section('title', ___('frontend.visa_services') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.visa_meta_description'))
@section('page', 'visa')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.visa_services'),
    'subtitle' => ___('frontend.visa_services_subtitle'),
    'crumbs' => [___('frontend.visa_services') => null],
])

{{-- Process steps — the fixed service workflow, not per-country content. --}}
<section class="section section-space">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-list-check"></i> {{ ___('frontend.how_it_works') }}</span>
            <h2>{{ ___('frontend.your_visa_steps') }}</h2>
        </div>
        <div class="row g-4">
            @foreach($steps as $i => $step)
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                <div class="service-card text-center h-100">
                    <div class="sc-icon mx-auto"><i class="fa-solid {{ $step->icon ?: 'fa-circle-check' }}"></i></div>
                    <div class="badge-tv mb-2">{{ ___('frontend.step_word') }} {{ $i + 1 }}</div>
                    <h4>{{ $step->title }}</h4>
                    <p>{{ $step->body }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Country grid (CMS: Visa Services) --}}
<section class="section section-space section--tint">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-earth-americas"></i> {{ ___('frontend.destinations_word') }}</span>
            <h2>{{ ___('frontend.popular_visa_destinations') }}</h2>
            <p>{{ ___('frontend.visa_destinations_copy') }}</p>
        </div>

        <form action="{{ route('front.visa') }}" method="get" class="enquiry-card mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6 field">
                    <label class="field-label">{{ ___('frontend.country') }}</label>
                    <select class="form-select select2" name="q">
                        <option value="">{{ ___('frontend.any') }}</option>
                        @foreach($countries as $c)
                            <option value="{{ $c }}" @selected(request('q') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 field">
                    <label class="field-label">{{ ___('frontend.visa_type') }}</label>
                    <select class="form-select" name="type">
                        <option value="">{{ ___('frontend.any_type') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-brand btn-block"><i class="fa-solid fa-magnifying-glass"></i> {{ ___('frontend.find') }}</button></div>
            </div>
        </form>

        <div class="row g-4">
            @forelse($services as $service)
            <div class="col-md-6 col-lg-3" data-aos="fade-up">
                <div class="card card-hover h-100 p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="visa-flag">{{ $service->flag ?: '🌍' }}</div>
                        @if($service->fee > 0)
                            <div class="text-end">
                                <span class="badge-tv is-accent">{{ currency_symbol() }}{{ number_format($service->fee) }}</span>
                                {{-- The badge used to be a bare number; applicants read it as
                                     the service charge and were surprised by the embassy fee. --}}
                                @if($service->govt_fee > 0 && $service->service_fee > 0)
                                    <div class="text-muted-2 text-11 mt-1">
                                        {{ ___('frontend.incl_embassy_fee') }} {{ currency_symbol() }}{{ number_format($service->govt_fee) }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                    <h5 class="mb-1">{{ $service->country }}</h5>
                    <div class="text-muted-2 text-14 mb-1">{{ $service->visa_type }}</div>
                    <div class="text-muted-2 text-13 mb-3">
                        @if($service->processing_time)
                            <div><i class="fa-regular fa-clock"></i> {{ ___('frontend.processing') }}: {{ $service->processing_time }}</div>
                        @endif
                        @if($service->stay_duration)
                            <div><i class="fa-regular fa-calendar-check"></i> {{ ___('frontend.stay') }}: {{ $service->stay_duration }}</div>
                        @endif
                        @if($service->entry_type)
                            <div><i class="fa-solid fa-right-to-bracket"></i> {{ $service->entry_type }} {{ ___('frontend.entry') }}</div>
                        @endif
                    </div>
                    <a href="{{ route('front.book', ['type' => 'visa', 'country' => $service->country, 'visa_type' => $service->visa_type]) }}"
                       class="btn btn-soft btn-sm btn-block mt-auto">{{ ___('frontend.apply_now') }}</a>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-passport fa-2x text-muted-2 mb-3"></i>
                <p class="text-muted-2 mb-3">
                    {{ request('q') || request('type') ? ___('frontend.no_visa_matched') : ___('frontend.no_visa_published') }}
                </p>
                <a href="{{ route('front.book', 'visa') }}" class="btn btn-brand">{{ ___('frontend.tell_us_where_going') }}</a>
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="section section-space">
    <div class="container">
        <div class="cta-band">
            <h2 class="mb-2">{{ ___('frontend.not_sure_visa') }}</h2>
            <p class="mb-4">{{ ___('frontend.visa_consultation_copy') }}</p>
            <a href="{{ route('front.contact') }}" class="btn btn-white btn-lg">{{ ___('frontend.get_free_consultation') }}</a>
        </div>
    </div>
</section>
@endsection

