@extends('frontend.layouts.master')
@section('title', ___('frontend.faq_title') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.faq_meta_description'))
@section('page', 'faq')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.frequently_asked_questions'),
    'subtitle' => ___('frontend.faq_hero_subtitle'),
    'crumbs' => [___('frontend.faq_title') => null],
])

<section class="section section-space">
    <div class="container faq-container">

        @if($categories->count() > 1)
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
            <a href="{{ route('front.faq') }}" class="btn btn-sm {{ request('category') ? 'btn-outline-brand' : 'btn-brand' }}">{{ ___('frontend.all') }}</a>
            @foreach($categories as $cat)
                <a href="{{ route('front.faq', ['category' => $cat]) }}"
                   class="btn btn-sm {{ request('category') === $cat ? 'btn-brand' : 'btn-outline-brand' }}">{{ $cat }}</a>
            @endforeach
        </div>
        @endif

        <div class="faq accordion" id="faqAcc">
            @forelse($faqs as $i => $faq)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->id }}">
                        {{ $faq->question }}
                    </button>
                </h2>
                <div id="faq{{ $faq->id }}" class="accordion-collapse collapse {{ $i == 0 ? 'show' : '' }}" data-bs-parent="#faqAcc">
                    <div class="accordion-body">{{ $faq->answer }}</div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fa-regular fa-circle-question fa-2x text-muted-2 mb-3"></i>
                <p class="text-muted-2 mb-0">{{ ___('frontend.no_questions') }}</p>
            </div>
            @endforelse
        </div>

        <div class="text-center mt-5">
            <p class="text-muted-2">{{ ___('frontend.still_have_questions') }}</p>
            <a href="{{ route('front.contact') }}" class="btn btn-brand">{{ ___('frontend.contact_our_team') }}</a>
        </div>
    </div>
</section>

@if($faqs->isNotEmpty())
@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $faqs->map(fn ($f) => [
        '@type'          => 'Question',
        'name'           => $f->question,
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string) $f->answer],
    ])->values(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
@endif
@endsection

