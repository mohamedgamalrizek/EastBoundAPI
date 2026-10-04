@extends('frontend.layouts.master')
@section('title', ___('frontend.tour_packages') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.packages_meta_description'))
@section('page', 'packages')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.tour_packages'),
    'subtitle' => ___('frontend.tour_packages_subtitle'),
    'crumbs' => [___('frontend.tour_packages') => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-4">
            {{-- Sidebar filters --}}
            <div class="col-lg-3">
                <form action="{{ route('front.packages') }}" method="get" class="card p-3 lg:p-6 sticky-header-offset">
                    <h5 class="mb-3">{{ ___('frontend.filter') }}</h5>
                    <div class="mb-3">
                        <label class="form-label">{{ ___('frontend.search') }}</label>
                        <div class="input-icon"><i class="fa-solid fa-magnifying-glass"></i><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ ___('frontend.destination_placeholder') }}"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ ___('frontend.category') }}</label>
                        <select class="form-select" name="category">
                            <option value="">{{ ___('frontend.all_categories') }}</option>
                            {{-- Package Categories that have a live package. --}}
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected((string) request('category') === (string) $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ ___('frontend.sort_by') }}</label>
                        <select class="form-select" name="sort">
                            <option value="latest" @selected(request('sort')==='latest')>{{ ___('frontend.newest') }}</option>
                            <option value="price_low" @selected(request('sort')==='price_low')>{{ ___('frontend.price_low_to_high') }}</option>
                            <option value="price_high" @selected(request('sort')==='price_high')>{{ ___('frontend.price_high_to_low') }}</option>
                        </select>
                    </div>
                    <button class="btn btn-brand btn-block">{{ ___('frontend.apply_filters') }}</button>
                    <a href="{{ route('front.packages') }}" class="btn btn-ghost btn-block mt-2">{{ ___('frontend.reset') }}</a>
                </form>
            </div>

            {{-- Results --}}
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted-2 mb-0">
                        {{ ___('frontend.showing_word') }} <strong>{{ $packages->count() }}</strong> {{ ___('label.of') }} {{ $packages->total() }} {{ ___('frontend.packages_word') }}
                        @if(request('q')) {{ ___('frontend.for_word') }} “<strong>{{ request('q') }}</strong>”@endif
                    </p>
                </div>
                <div class="row g-3 g-md-4">
                    @forelse($packages as $package)
                        <div class="col-6 col-sm-6 col-xl-4" data-aos="fade-up">
                            @include('frontend.components.package-card', ['package' => $package])
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card p-5 text-center text-muted-2">
                                <div class="mb-2 empty-state-icon"><i class="fa-solid fa-suitcase-rolling"></i></div>
                                {{ ___('frontend.no_packages_found') }}
                            </div>
                        </div>
                    @endforelse
                </div>
                <div class="mt-5 d-flex justify-content-center">
                    {{ $packages->withQueryString()->links('pagination::bootstrap-5') }}
                </div>

                {{-- Custom tour CTA --}}
                <div class="cta-band mt-5">
                    <span class="badge-tv is-accent mb-2"><i class="fa-solid fa-route"></i> {{ ___('frontend.cant_find_perfect_trip') }}</span>
                    <h2 class="mb-2">{{ ___('frontend.request_custom_tour') }}</h2>
                    <p class="mb-4">{{ ___('frontend.custom_tour_copy') }}</p>
                    <a href="{{ route('front.book', 'custom-tour') }}" class="btn btn-white btn-lg">{{ ___('frontend.plan_custom_trip') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

