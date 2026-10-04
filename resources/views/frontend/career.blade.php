@extends('frontend.layouts.master')
@section('title', ___('frontend.career_title') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.career_meta_description'))
@section('page', 'career')

@section('content')
@include('frontend.components.page-hero', [
    'title' => str_replace(':name', settings('name') ?: 'FLOW', ___('frontend.careers_at')),
    'subtitle' => ___('frontend.career_hero_subtitle'),
    'crumbs' => [___('frontend.career_title') => null],
])

<section class="section section-space">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-briefcase"></i> {{ ___('frontend.open_positions') }}</span>
            <h2>{{ $jobs->isEmpty() ? ___('frontend.no_open_roles') : ___('frontend.we_are_hiring') }}</h2>
        </div>

        <div class="row g-4">
            @forelse($jobs as $job)
            <div class="col-md-6 col-lg-4" data-aos="fade-up">
                <div class="job-card">
                    <h4 class="text-20 mb-0">{{ $job->title }}</h4>
                    <div class="job-meta">
                        @if($job->department)<span class="badge-tv">{{ $job->department }}</span>@endif
                        @if($job->location)<span class="badge-tv"><i class="fa-solid fa-location-dot"></i> {{ $job->location }}</span>@endif
                        <span class="badge-tv is-success">{{ $job->employment_type }}</span>
                    </div>
                    @if($job->description)
                        <p class="text-muted-2 text-14">{{ Str::limit($job->description, 110) }}</p>
                    @endif
                    @if($job->closing_date)
                        <div class="text-muted-2 text-13 mb-2"><i class="fa-regular fa-calendar"></i> {{ ___('frontend.apply_by_prefix') }} {{ dateFormat($job->closing_date) }}</div>
                    @endif
                    <a href="{{ route('front.career.apply', $job) }}" class="btn btn-outline-brand btn-sm btn-block">{{ ___('frontend.apply_now') }}</a>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-4">
                <i class="fa-solid fa-briefcase fa-2x text-muted-2 mb-3"></i>
                <p class="text-muted-2 mb-0">{{ ___('frontend.no_vacancies') }}</p>
            </div>
            @endforelse
        </div>

        <div class="text-center mt-5">
            <p class="text-muted-2">{{ ___('frontend.dont_see_role') }}</p>
            <a href="{{ route('front.contact') }}" class="btn btn-brand">{{ ___('frontend.send_your_cv') }}</a>
        </div>
    </div>
</section>
@endsection

