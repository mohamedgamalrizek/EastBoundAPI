@extends('frontend.layouts.master')
@section('title', ___('frontend.umrah_packages') . ' — FLOW')
@section('meta', ___('frontend.umrah_meta_description'))
@section('page', 'umrah')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.umrah_packages'),
    'subtitle' => ___('frontend.umrah_packages_subtitle'),
    'crumbs' => [___('frontend.umrah') => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-4">
            @php $icons = ['fa-mosque', 'fa-kaaba', 'fa-star', 'fa-moon']; @endphp
            @forelse($packages as $i => $p)
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $i*70 }}">
                <div class="service-card h-100 text-center">
                    <div class="sc-icon mx-auto"><i class="fa-solid {{ $icons[$i % count($icons)] }}"></i></div>
                    <h4 class="text-20">{{ $p->title }}</h4>
                    <p>{{ $p->duration_days }} {{ ___('label.days') }} · {{ $p->seats }} {{ ___('label.seats') }}</p>
                    <div class="pc-price my-2">{{ currency_symbol() }}{{ number_format((float) $p->price) }}</div>
                    <a href="{{ route('front.book', 'umrah') }}" class="btn btn-soft btn-sm btn-block">{{ ___('frontend.register') }}</a>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card p-5 text-center text-muted-2">
                    {{ ___('frontend.no_umrah_packages') }}
                </div>
            </div>
            @endforelse
        </div>

        <div class="row g-4 mt-4 md:mt-12">
            <div class="col-lg-6 why-media" data-aos="fade-right">
                <img src="{{ placeholder_image('slide') }}" alt="{{ ___('frontend.umrah') }}" loading="lazy">
            </div>
            <div class="col-lg-6 lg:pl-12" data-aos="fade-left">
                <span class="eyebrow"><i class="fa-solid fa-circle-check"></i> {{ ___('frontend.included_in_every_package') }}</span>
                <h2 class="mt-2 mb-3">{{ ___('frontend.everything_taken_care') }}</h2>
                <div class="d-flex flex-column gap-3">
                    @foreach($includes as $f)
                    <div class="feature-item"><div class="fi-icon"><i class="fa-solid {{ $f->icon ?: 'fa-circle-check' }}"></i></div><div><h5 class="mb-0 text-16">{{ $f->title }}</h5></div></div>
                    @endforeach
                </div>
                <a href="{{ route('front.book', 'umrah') }}" class="btn btn-brand mt-4">{{ ___('frontend.book_your_umrah') }}</a>
            </div>
        </div>
    </div>
</section>
@endsection

