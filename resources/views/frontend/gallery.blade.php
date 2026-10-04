@extends('frontend.layouts.master')
@section('title', ___('frontend.gallery_page_title') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.gallery_meta_description'))
@section('page', 'gallery')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.photo_gallery'),
    'subtitle' => ___('frontend.gallery_subtitle'),
    'crumbs' => [___('frontend.gallery_page_title') => null],
])

<section class="section section-space">
    <div class="container">

        @if($categories->count() > 1)
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
            <a href="{{ route('front.gallery') }}" class="btn btn-sm {{ request('category') ? 'btn-outline-brand' : 'btn-brand' }}">{{ ___('frontend.all') }}</a>
            @foreach($categories as $cat)
                <a href="{{ route('front.gallery', ['category' => $cat]) }}"
                   class="btn btn-sm {{ request('category') === $cat ? 'btn-brand' : 'btn-outline-brand' }}">{{ $cat }}</a>
            @endforeach
        </div>
        @endif

        <div class="row g-3">
            @forelse($images as $i => $image)
            @php $src = media_url($image->image, placeholder_image('card')); @endphp
            <div class="col-6 col-md-4 col-lg-3" data-aos="zoom-in" data-aos-delay="{{ ($i % 4) * 60 }}">
                <a href="{{ $src }}" target="_blank" rel="noopener" class="gallery-item">
                    <img src="{{ $src }}" alt="{{ $image->image_label ?: $image->title }}" loading="lazy">
                </a>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fa-regular fa-images fa-2x text-muted-2 mb-3"></i>
                <p class="text-muted-2 mb-0">{{ ___('frontend.no_photos') }}</p>
            </div>
            @endforelse
        </div>
    </div>
</section>
@endsection

