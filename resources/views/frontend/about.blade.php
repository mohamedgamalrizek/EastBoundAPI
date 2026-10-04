@extends('frontend.layouts.master')
@section('title', ($page?->title ?: ___('frontend.about_us')) . ' - ' . (settings('name') ?: 'FLOW'))
@section('meta', $page?->meta_description ?: ___('frontend.about_meta_description'))
@section('page', 'about')

@section('content')
@include('frontend.components.page-hero', [
    'title' => $page?->title ?: str_replace(':name', settings('name') ?: 'FLOW', ___('frontend.about_name')),
    'subtitle' => $page?->hero_subtitle ?: ___('frontend.about_hero_subtitle'),
    'crumbs' => [($page?->breadcrumb_label ?: ___('frontend.about_us')) => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 why-media" data-aos="fade-right">
                <img src="{{ media_url($page?->story_image, placeholder_image('slide')) }}"
                     alt="{{ (settings('name') ?: 'FLOW') . ' ' . ___('frontend.team_word') }}" loading="lazy">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="eyebrow"><i class="fa-solid fa-book-open"></i> {{ $page?->story_eyebrow ?: ___('frontend.our_story') }}</span>
                <h2 class="mt-2 mb-3">{{ $page?->story_heading ?: ___('frontend.about_story_heading') }}</h2>
                @if($page?->story_lead)
                    <p class="lead">{{ $page->story_lead }}</p>
                @endif
                @if($page?->story_body)
                    <p class="text-muted-2">{{ $page->story_body }}</p>
                @endif

                @php
                    $badges = collect(explode(',', (string) ($page?->story_badges ?? '')))
                        ->map(fn ($badge) => trim($badge))
                        ->filter();
                    $badgeStyles = ['', 'is-success', 'is-accent'];
                @endphp
                @if($badges->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @foreach($badges as $index => $badge)
                            <span class="badge-tv {{ $badgeStyles[$index % count($badgeStyles)] }}"><i class="fa-solid fa-circle-check"></i> {{ $badge }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="section section-space pt-0">
    <div class="container">
        <div class="stats-band">
            <div class="row g-4 text-center">
                @foreach($stats as $stat)
                    <div class="col-6 col-lg-3">
                        <div class="stat">
                            <div class="stat-num"><span data-count="{{ $stat['num'] }}" data-suffix="{{ $stat['suffix'] }}">0</span></div>
                            <div class="stat-label">{{ $stat['label'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="section section-space section--tint">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><i class="fa-solid fa-heart"></i> {{ ___('frontend.our_values') }}</span>
            <h2>{{ ___('frontend.what_we_stand_for') }}</h2>
        </div>
        <div class="row g-4">
            @foreach($values as $value)
                <div class="col-md-4" data-aos="fade-up">
                    <div class="service-card h-100">
                        <div class="sc-icon"><i class="fa-solid {{ $value->icon ?: 'fa-star' }}"></i></div>
                        <h4>{{ $value->title }}</h4>
                        <p>{{ $value->body }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

