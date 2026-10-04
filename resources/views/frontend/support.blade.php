@extends('frontend.layouts.master')
@section('title', ___('frontend.support_center_title') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.support_meta_description'))
@section('page', 'support')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.support_center_title'),
    'subtitle' => ___('frontend.support_hero_subtitle'),
    'crumbs' => [___('label.support') => null],
])

<section class="section section-space">
    <div class="container">
        {{-- Navigation shortcuts — these point at fixed site features, not content. --}}
        <div class="row g-4 mb-2">
            @foreach($topics as $i => $topic)
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $i * 70 }}">
                <a href="{{ $topic->href() ?? '#' }}" class="service-card d-block h-100 text-center">
                    <div class="sc-icon mx-auto"><i class="fa-solid {{ $topic->icon ?: 'fa-circle-question' }}"></i></div>
                    <h4 class="text-20">{{ $topic->title }}</h4><p>{{ $topic->body }}</p>
                </a>
            </div>
            @endforeach
        </div>

        <div class="row g-4 align-items-center mt-3">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="fa-solid fa-headset"></i> {{ ___('frontend.talk_to_us') }}</span>
                <h2 class="mt-2 mb-3">{{ ___('frontend.support_247_heading') }}</h2>
                <p class="text-muted-2">{{ ___('frontend.support_intro') }}</p>
                <div class="d-flex flex-column gap-3 mt-3">
                    @if(settings('phone'))
                    <div class="feature-item">
                        <div class="fi-icon"><i class="fa-solid fa-phone"></i></div>
                        <div><h5 class="mb-0 text-16">{{ settings('phone') }}</h5><span class="text-muted-2 text-14">{{ ___('frontend.support_hours') }}</span></div>
                    </div>
                    @endif
                    @if(settings('whatsapp'))
                    <div class="feature-item">
                        <div class="fi-icon"><i class="fa-brands fa-whatsapp"></i></div>
                        <div><h5 class="mb-0 text-16">{{ settings('whatsapp') }}</h5><span class="text-muted-2 text-14">{{ ___('frontend.instant_replies') }}</span></div>
                    </div>
                    @endif
                    @if(settings('email'))
                    <div class="feature-item">
                        <div class="fi-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div><h5 class="mb-0 text-16">{{ settings('email') }}</h5><span class="text-muted-2 text-14">{{ ___('frontend.replies_within_hour') }}</span></div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <div class="enquiry-card">
                    <h4 class="mb-3">{{ ___('frontend.send_support_request') }}</h4>
                    <a href="{{ route('front.contact') }}" class="btn btn-brand btn-block">{{ ___('frontend.open_ticket') }}</a>
                    <p class="text-muted-2 text-14 text-center mt-2 mb-0">{{ ___('frontend.average_response') }}</p>
                </div>
            </div>
        </div>

        {{-- Latest published FAQs, so the support page reflects the real help content. --}}
        @if($faqs->isNotEmpty())
        <div class="mt-5">
            <div class="section-head text-start">
                <span class="eyebrow"><i class="fa-solid fa-circle-question"></i> {{ ___('frontend.quick_answers') }}</span>
                <h2>{{ ___('frontend.common_questions') }}</h2>
            </div>
            <div class="accordion faq" id="supportFaq">
                @foreach($faqs as $i => $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sfaq{{ $faq->id }}">
                            {{ $faq->question }}
                        </button>
                    </h2>
                    <div id="sfaq{{ $faq->id }}" class="accordion-collapse collapse {{ $i == 0 ? 'show' : '' }}" data-bs-parent="#supportFaq">
                        <div class="accordion-body">{{ $faq->answer }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('front.faq') }}" class="btn btn-outline-brand">{{ ___('frontend.see_all_faqs') }} <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

