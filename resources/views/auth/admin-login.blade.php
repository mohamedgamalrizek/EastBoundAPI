@extends('auth.saas-layout')
@section('title') {{ ___('menus.login') }} @endsection

@section('brand_heading')
    @php
        $heading = ___('auth.admin_brand_heading');
    @endphp
    <h1>{!! nl2br(e($heading)) !!}</h1>
    <p class="sub">{{ ___('auth.admin_brand_subtitle') }}</p>
@endsection

@section('form')
    <div class="top-link">
        {{ ___('auth.admin_top_text') }}
        <a href="{{ route('loginForm') }}">{{ ___('auth.admin_top_link') }}</a>
    </div>

    <h2>{{ ___('auth.admin_form_title') }}</h2>
    <p class="lead-sm">{{ ___('auth.admin_form_intro') }}</p>

    <form action="{{ route('admin.login') }}" method="POST" data-demo>
        @csrf

        <div class="form-group">
            <label class="form-label">{{ ___('label.Email') }}</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                   placeholder="{{ ___('placeholder.enter_email') }}" autofocus>
            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="row-between mb-1">
                <label class="form-label mb-0">{{ ___('label.Password') }}</label>
                <a class="forgot" href="{{ route('password.forgotForm') }}">{{ ___('label.Forgot Password') }}?</a>
            </div>
            <div class="input-wrap">
                <input type="password" name="password" id="apwd" class="form-control"
                       placeholder="{{ ___('placeholder.enter_password') }}">
                <span class="toggle" onclick="var p=document.getElementById('apwd');p.type=p.type==='password'?'text':'password';this.textContent=p.type==='password'?'{{ ___('frontend.show') }}':'{{ ___('frontend.hide') }}';">{{ ___('frontend.show') }}</span>
            </div>
            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="row-between mb-4 mt-3">
            <label class="remember mb-0">
                <input type="checkbox" name="remember" @checked(old('remember')) class="mr-1">
                {{ ___('label.Remember Me') }}
            </label>
        </div>

        <button type="submit" class="btn-blue">{{ ___('auth.admin_submit_button') }}</button>
    </form>

    @include('auth.partials.demo-logins', ['adminOnly' => true])
@endsection
