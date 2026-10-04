@extends('auth.saas-layout')
@section('title') {{ ___('frontend.reset_password') }} @endsection

@section('brand_heading')
    <h1>{!! ___('frontend.reset_brand') !!}</h1>
    <p class="sub">{{ ___('frontend.reset_copy') }}</p>
@endsection

@section('form')
    <div class="top-link">
        {{ ___('frontend.remembered_it') }} <a href="{{ route('loginForm') }}">{{ ___('label.signin') }}</a>
    </div>

    <h2>{{ ___('frontend.reset_password') }}</h2>
    <p class="lead-sm">{{ ___('frontend.reset_lead') }}</p>

    {{-- Hard URL (not route name): the app's POST password/reset shares the
         name 'password.reset' with a Fortify route, so route() is ambiguous. --}}
    <form method="POST" action="{{ url('password/reset') }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ session('user_id') }}">
        <input type="hidden" name="token" value="{{ session('token') }}">

        <div class="form-group">
            <label class="form-label">{{ ___('frontend.new_password') }} <span class="text-danger">*</span></label>
            <div class="input-wrap">
                <input type="password" name="new_password" id="npwd" class="form-control"
                       placeholder="{{ ___('placeholder.Enter new password') }}" required autocomplete="off" autofocus>
                <span class="toggle" onclick="var p=document.getElementById('npwd');p.type=p.type==='password'?'text':'password';this.textContent=p.type==='password'?'{{ ___('frontend.show') }}':'{{ ___('frontend.hide') }}';">{{ ___('frontend.show') }}</span>
            </div>
            @error('new_password') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ ___('frontend.confirm_password') }} <span class="text-danger">*</span></label>
            <input type="password" name="confirm_password" class="form-control"
                   placeholder="{{ ___('placeholder.Confirm new password') }}" required autocomplete="off">
            @error('confirm_password') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn-blue mt-2">{{ ___('frontend.submit') }} →</button>
    </form>

    <div class="divider">{{ ___('frontend.or') }}</div>

    <p class="signup-line mb-0">
        {{ ___('frontend.know_your_password') }}
        <a href="{{ route('loginForm') }}">{{ ___('label.signin') }}</a>
    </p>
@endsection
