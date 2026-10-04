{{-- Generic CMS-managed static page (privacy, terms, refund, cancellation…).
     Expects: $title, $body (already-rendered HTML or null), $page (CmsPage|null). --}}
@extends('frontend.layouts.master')
@section('title', $title . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', $page->meta_description ?? $title)
@section('page', 'cms-page')

@section('content')
@include('frontend.components.page-hero', ['title' => $title, 'crumbs' => [$title => null]])

<section class="section section-space">
    <div class="container">
        <div class="prose mx-auto">
            @if($body)
                @if($page?->updated_at)
                    <p class="text-muted-2">{{ ___('frontend.last_updated_prefix') }} {{ dateFormat($page->updated_at) }}</p>
                @endif
                {{-- Markdown rendered server-side with raw HTML stripped. --}}
                {!! $body !!}
            @else
                <div class="text-center py-4">
                    <i class="fa-regular fa-file-lines fa-2x text-muted-2 mb-3"></i>
                    <p class="text-muted-2 mb-3">{{ ___('frontend.unpublished_page') }}</p>
                    <a href="{{ route('front.contact') }}" class="btn btn-brand">{{ ___('frontend.contact_us_details') }}</a>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

