@extends('auth.saas-layout')

{{--
    The one code-entry screen for the whole system.

    Account verification after signup and password reset ask for exactly the
    same thing — a six-digit code that was emailed — so they share this view
    instead of keeping two near-identical copies that drift apart. The caller
    passes $purpose ('register' or 'reset'); everything else is derived.
--}}

@php
    $isReset = ($purpose ?? 'register') === 'reset';
    $action  = $isReset ? route('password.verifyToken') : route('register.verifyToken');
@endphp

@section('title') {{ ___('auth.otp_page_title') }} @endsection

@section('brand_heading')
    <h1>{!! nl2br(e(___('auth.verify_brand_heading'))) !!}</h1>
    <p class="sub">{{ ___('auth.verify_brand_subtitle') }}</p>
@endsection

@section('form')
    @if($isReset)
        <div class="top-link">
            {{ ___('frontend.remembered_it') }} <a href="{{ route('login') }}">{{ ___('label.signin') }}</a>
        </div>
    @endif

    <h2>{{ $isReset ? ___('auth.otp_title_reset') : ___('auth.verify_form_title') }}</h2>
    <p class="lead-sm">
        {{ $isReset ? ___('auth.otp_intro_reset') : ___('auth.verify_form_intro') }}
        <b>{{ session('email') }}</b>.
    </p>

    <form method="POST" action="{{ $action }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ session('user_id') }}">

        <div class="form-group">
            <label class="form-label">{{ ___('auth.verify_code_label') }} <span class="text-danger">*</span></label>
            <input type="text" name="token" id="token" class="form-control" value="{{ old('token') }}"
                   placeholder="{{ ___('auth.verify_code_placeholder') }}" required autocomplete="one-time-code"
                   inputmode="numeric" pattern="[0-9]*" maxlength="6" autofocus>
            @error('token') <span class="text-danger small">{{ $message }}</span> @enderror
            @if(session()->has('danger')) <span class="text-danger small">{{ session('danger') }}</span> @endif
        </div>

        <button type="submit" class="btn-blue mt-2">{{ ___('auth.verify_submit_button') }}</button>
    </form>

    <div class="divider">{{ ___('auth.verify_divider_text') }}</div>

    <p class="signup-line mb-0">
        {{ ___('auth.verify_resend_text') }}
        <a id="resendToken" href="{{ route('token.resend') }}" onclick="resendToken(event)"
           data-user-id="{{ session('user_id') }}" data-csrf-token="{{ csrf_token() }}">{{ ___('auth.verify_resend_link') }}</a>
    </p>
@endsection
