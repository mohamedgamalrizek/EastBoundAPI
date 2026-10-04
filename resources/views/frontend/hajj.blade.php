@extends('frontend.layouts.master')
@section('title', ___('frontend.hajj_packages') . ' — FLOW')
@section('meta', ___('frontend.hajj_meta_description'))
@section('page', 'hajj')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.hajj_packages'),
    'subtitle' => ___('frontend.hajj_packages_subtitle'),
    'crumbs' => [___('frontend.hajj') => null],
])

<section class="section section-space">
    <div class="container">
        <div class="usp-row justify-content-center mb-5">
            <span class="usp"><i class="fa-solid fa-building-shield"></i> {{ ___('frontend.govt_approved_agency') }}</span>
            <span class="usp"><i class="fa-solid fa-hotel"></i> {{ ___('frontend.hotels_near_haram') }}</span>
            <span class="usp"><i class="fa-solid fa-user-group"></i> {{ ___('frontend.experienced_guides') }}</span>
            <span class="usp"><i class="fa-solid fa-plane"></i> {{ ___('frontend.direct_flights') }}</span>
        </div>

        <div class="row g-4">
            @forelse($packages as $i => $p)
            @php $featured = $packages->count() > 1 && $i === 1; @endphp
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="{{ $i*80 }}">
                <div class="card h-100 p-4 {{ $featured ? 'border-2 package-card-featured' : '' }}">
                    @if($featured)<span class="badge-tv is-accent mb-2">{{ ___('frontend.most_popular') }}</span>@endif
                    <h4>{{ $p->title }}</h4>
                    <p class="text-muted-2 text-14 mb-2">{{ $p->package_no }} · {{ $p->duration_days }} {{ ___('label.days') }}</p>
                    <div class="pc-price mb-3">{{ currency_symbol() }}{{ number_format((float) $p->price) }} <small>/ person</small></div>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-4">
                        <li><i class="fa-solid fa-check me-2 text-success-tv"></i>{{ $p->duration_days }}-{{ ___('frontend.day_programme_suffix') }}</li>
                        <li><i class="fa-solid fa-check me-2 text-success-tv"></i>{{ $p->seats }} {{ ___('frontend.seats_available_suffix') }}</li>
                        <li><i class="fa-solid fa-check me-2 text-success-tv"></i>{{ ___('frontend.visa_air_ticket_processing') }}</li>
                        <li><i class="fa-solid fa-check me-2 text-success-tv"></i>{{ ___('frontend.hotels_transport_guide_included') }}</li>
                    </ul>
                    <a href="{{ route('front.book', 'hajj') }}" class="btn {{ $featured ? 'btn-brand' : 'btn-outline-brand' }} btn-block mt-auto">{{ ___('frontend.register_now') }}</a>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card p-5 text-center text-muted-2">
                    {{ ___('frontend.no_hajj_packages') }}
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- What every Hajj package covers — CMS → Content Blocks (hajj_includes). --}}
@if($includes->isNotEmpty())
<section class="section section-space section--tint pt-0">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-circle-check"></i> {{ ___('frontend.included') }}</span>
            <h2>{{ ___('frontend.what_every_package_covers') }}</h2>
        </div>
        <div class="row g-3 justify-content-center">
            @foreach($includes as $f)
            <div class="col-md-6 col-lg-4" data-aos="fade-up">
                <div class="feature-item">
                    <div class="fi-icon"><i class="fa-solid {{ $f->icon ?: 'fa-circle-check' }}"></i></div>
                    <div><h5 class="mb-0 text-16">{{ $f->title }}</h5></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section section-space pt-0">
    <div class="container"><div class="cta-band">
        <h2 class="mb-2">{{ ___('frontend.need_custom_hajj') }}</h2>
        <p class="mb-4">{{ ___('frontend.custom_hajj_copy') }}</p>
        <a href="{{ route('front.contact') }}" class="btn btn-white btn-lg">{{ ___('frontend.talk_hajj_consultant') }}</a>
    </div></div>
</section>
@endsection

