@extends('frontend.layouts.master')
@section('title', ($page?->title ?: ___('frontend.blog_page_title')) . ' - ' . (settings('name') ?: 'FLOW'))
@section('meta', $page?->meta_description ?: ___('frontend.blog_meta_description'))
@section('page', 'blog')

@section('content')
@include('frontend.components.page-hero', [
    'title' => $page?->title ?: ___('frontend.blog_page_title'),
    'subtitle' => $page?->hero_subtitle ?: ___('frontend.blog_hero_subtitle'),
    'crumbs' => [($page?->breadcrumb_label ?: ___('frontend.blog_crumb')) => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-3 align-items-end mb-4">
            <div class="col-md-6">
                <form action="{{ route('front.blog') }}" method="get" class="input-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ $page?->search_placeholder ?: ___('frontend.search_articles_placeholder') }}">
                    @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                </form>
            </div>
            @if($categories->count())
                <div class="col-md-6">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <a href="{{ route('front.blog') }}" class="btn btn-sm {{ request('category') ? 'btn-outline-brand' : 'btn-brand' }}">{{ $page?->all_label ?: ___('frontend.all') }}</a>
                        @foreach($categories as $cat)
                            <a href="{{ route('front.blog', ['category' => $cat]) }}"
                               class="btn btn-sm {{ request('category') === $cat ? 'btn-brand' : 'btn-outline-brand' }}">{{ $cat }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="row g-4">
            @forelse($posts as $post)
                @php $cover = media_url($post->image, placeholder_image('card')); @endphp
                <div class="col-md-6 col-lg-4" data-aos="fade-up">
                    <article class="card blog-card card-hover h-100">
                        <a href="{{ route('front.blog.show', $post->slug) }}" class="bc-media">
                            <img src="{{ $cover }}" alt="{{ $post->title }}" loading="lazy">
                        </a>
                        <div class="bc-body">
                            <div class="bc-meta">
                                @if($post->category)<span class="badge-tv">{{ $post->category }}</span>@endif
                                <i class="fa-regular fa-calendar ms-2"></i> {{ $post->published_at ? dateFormat($post->published_at) : dateFormat($post->created_at) }}
                            </div>
                            <h3 class="bc-title"><a href="{{ route('front.blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                            @if($post->excerpt)
                                <p class="text-muted-2 text-14">{{ $post->excerpt }}</p>
                            @endif
                            <a href="{{ route('front.blog.show', $post->slug) }}" class="fw-700 text-14 link-primary-dark">{{ $page?->read_more_label ?: ___('frontend.read_more') }} <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fa-regular fa-newspaper fa-2x text-muted-2 mb-3"></i>
                    <p class="text-muted-2 mb-0">
                        {{ request('q') || request('category') ? ($page?->filtered_empty_text ?: ___('frontend.no_articles_matched')) : ($page?->empty_text ?: ___('frontend.no_articles_published')) }}
                    </p>
                </div>
            @endforelse
        </div>

        @if($posts->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</section>
@endsection

