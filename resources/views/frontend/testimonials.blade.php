@extends('frontend.layouts.master')
@section('title', ___('frontend.testimonials') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.testimonials_meta_description'))
@section('page', 'testimonials')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.what_our_travelers_say'),
    'subtitle' => ___('frontend.testimonials_subtitle'),
    'crumbs' => [___('frontend.testimonials') => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-4">
            @forelse($reviews as $review)
            <div class="col-md-6 col-lg-4" data-aos="fade-up">
                @include('frontend.components.review-card', ['review' => $review])
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fa-regular fa-comment fa-2x text-muted-2 mb-3"></i>
                <p class="text-muted-2 mb-0">{{ ___('frontend.no_reviews') }}</p>
            </div>
            @endforelse
        </div>

        @if($reviews->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</section>
@endsection

