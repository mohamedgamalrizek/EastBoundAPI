{{-- Reusable page hero + breadcrumb.
     Usage: @include('frontend.components.page-hero', ['title'=>'About Us','subtitle'=>'...','crumbs'=>['Tour Packages'=>route('front.packages'),'Maldives'=>null]]) --}}
@php $crumbs = $crumbs ?? []; @endphp
<section class="page-hero">
    <div class="container position-relative">
        <h1 data-aos="fade-up">{{ $title }}</h1>
        @if(!empty($subtitle))
            <p class="lead text-white-50 mx-auto mt-2 mb-0 page-hero-subtitle" data-aos="fade-up" data-aos-delay="60">{{ $subtitle }}</p>
        @endif
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ ___('frontend.home') }}</a></li>
                @foreach($crumbs as $label => $url)
                    @if($url)
                        <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
</section>

{{-- JSON-LD BreadcrumbList --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    { "@@type": "ListItem", "position": 1, "name": "{{ ___('frontend.home') }}", "item": "{{ route('home') }}" }
    @foreach($crumbs as $label => $url)
    ,{ "@@type": "ListItem", "position": {{ $loop->index + 2 }}, "name": "{{ $label }}"@if($url), "item": "{{ $url }}"@endif }
    @endforeach
  ]
}
</script>

