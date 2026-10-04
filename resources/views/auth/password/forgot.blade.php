@extends('auth.saas-layout')
@section('title') {{ ___('label.Forgot Password') }} @endsection

@section('brand_heading')
    <h1>{!! ___('frontend.forgot_it') !!}</h1>
    <p class="sub">{{ ___('frontend.forgot_copy') }}</p>
@endsection

@section('form')
    <div class="top-link">
        {{ ___('frontend.remembered_it') }} <a href="{{ route('login') }}">{{ ___('label.signin') }}</a>
    </div>

    <h2>{{ ___('frontend.forgot_password') }}</h2>
    <p class="lead-sm">{{ ___('frontend.forgot_lead') }}</p>

    <form method="POST" action="{{ route('password.verify.email') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">{{ ___('label.Email') }} <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                   placeholder="{{ ___('placeholder.enter_email') }}" required autocomplete="off" autofocus>
            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn-blue mt-2">{{ ___('frontend.verify_email') }} →</button>
    </form>

    <div class="divider">{{ ___('frontend.or') }}</div>

    <p class="signup-line mb-0">
        {{ ___('frontend.know_your_password') }}
        <a href="{{ route('login') }}">{{ ___('label.signin') }}</a>
    </p>
@endsection
