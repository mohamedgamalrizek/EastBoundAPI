@extends('frontend.layouts.master')

@section('title', ($success ? 'Payment confirmed' : 'Payment not completed') . ' - ' . (settings('name') ?: 'FLOW'))
@section('page', 'payment-result')

@section('content')
{{--
    The mobile app opens the checkout in a web view and watches for this URL.
    It reads data-payment-status / data-payment-reference instead of scraping
    the copy, so wording can change without breaking the app.
--}}
<section class="section section-space"
         data-payment-status="{{ $success ? 'paid' : ($status ?: 'failed') }}"
         data-payment-reference="{{ $reference }}">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="service-card text-center p-5">
                    <div class="sc-icon mx-auto mb-3">
                        <i class="fa-solid {{ $success ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
                    </div>

                    <h3 class="mb-2">{{ $success ? 'Payment confirmed' : 'Payment not completed' }}</h3>

                    <p class="mb-3">{{ $message }}</p>

                    @if($reference)
                        <p class="text-muted mb-1"><small>Reference: {{ $reference }}</small></p>
                    @endif

                    @if($success && $amount)
                        <p class="mb-4"><strong>{{ currency_symbol() }}{{ number_format((float) $amount, 2) }}</strong></p>
                    @endif

                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        @if(!empty($from_app))
                            <a href="{{ $appLink }}" class="btn btn-primary">Return to the app</a>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">Back to site</a>
                        @else
                            <a href="{{ route('cust.invoices') }}" class="btn btn-primary">My invoices</a>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">Back to site</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if(!empty($from_app))
    {{--
        This checkout was started from the mobile app: try to hand the payer
        straight back via the app's flow:// scheme. The button above stays
        as the fallback for browsers that block scripted scheme launches.
    --}}
    <script>
        setTimeout(function () { window.location.href = @json($appLink); }, 600);
    </script>
@endif
@endsection
