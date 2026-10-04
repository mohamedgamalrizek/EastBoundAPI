@extends('frontend.layouts.master')
@section('title', $post->title . ' — ' . (settings('name') ?: 'FLOW') . ' Blog')
@section('meta', $post->excerpt ?: Str::limit(strip_tags((string) $post->body), 155))
@section('page', 'blog')

@section('content')
@include('frontend.components.page-hero', [
    'title' => $post->title,
    'crumbs' => [___('frontend.blog_crumb') => route('front.blog'), Str::limit($post->title, 24) => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-5 justify-content-center">
            <div class="col-lg-8">
                @php $cover = media_url($post->image, placeholder_image('wide')); @endphp
                <img src="{{ $cover }}" class="article-cover rounded-tv shadow-tv mb-4" alt="{{ $post->title }}">

                <div class="d-flex align-items-center flex-wrap gap-3 mb-4 text-muted-2 text-14">
                    <span><i class="fa-regular fa-calendar"></i> {{ dateFormat($post->published_at ?: $post->created_at) }}</span>
                    <span><i class="fa-regular fa-user"></i> {{ $post->author ?: ($post->authorUser->name ?? (settings('name') ?: 'FLOW') . ' Team') }}</span>
                    <span><i class="fa-regular fa-clock"></i> {{ $post->read_minutes ?: 4 }} {{ ___('frontend.min_read_suffix') }}</span>
                    @if($post->category)<span class="badge-tv">{{ $post->category }}</span>@endif
                </div>

                <div class="prose">
                    @if($post->excerpt)
                        <p class="lead">{{ $post->excerpt }}</p>
                    @endif

                    @if($body)
                        {{-- Markdown rendered server-side with raw HTML stripped. --}}
                        {!! $body !!}
                    @else
                        <p class="text-muted-2">{{ ___('frontend.article_coming_soon') }}</p>
                    @endif
                </div>

                @php $shareUrl = urlencode(route('front.blog.show', $post->slug)); @endphp
                <div class="d-flex align-items-center gap-2 mt-4">
                    <span class="fw-700">{{ ___('frontend.share') }}</span>
                    <a class="icon-action" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"><i class="fa-brands fa-facebook-f"></i></a>
                    <a class="icon-action" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ urlencode($post->title) }}"><i class="fa-brands fa-x-twitter"></i></a>
                    <a class="icon-action" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($post->title . ' ' . route('front.blog.show', $post->slug)) }}"><i class="fa-brands fa-whatsapp"></i></a>
                </div>

                @if($related->isNotEmpty())
                <div class="mt-5">
                    <h4 class="mb-3">{{ ___('frontend.related_reads') }}</h4>
                    <div class="row g-3">
                        @foreach($related as $item)
                        <div class="col-md-4">
                            <a href="{{ route('front.blog.show', $item->slug) }}" class="card card-hover h-100 p-3 d-block">
                                <div class="text-muted-2 text-13 mb-1">{{ $item->category ?: ___('label.article') }}</div>
                                <div class="fw-700 text-15">{{ Str::limit($item->title, 60) }}</div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card p-4 sticky-header-offset">
                    <h5 class="mb-3">{{ ___('frontend.plan_your_trip') }}</h5>
                    <p class="text-muted-2 text-14">{{ ___('frontend.plan_trip_copy') }}</p>
                    <a href="{{ route('front.packages') }}" class="btn btn-brand btn-block mb-2">{{ ___('frontend.browse_packages') }}</a>
                    <a href="{{ route('front.contact') }}" class="btn btn-soft btn-block">{{ ___('frontend.talk_to_expert') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

