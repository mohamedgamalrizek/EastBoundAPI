@extends('auth.saas-layout')
@section('title') {{ ___('menus.login') }} @endsection

@section('brand_heading')
    @php
        $heading = ___('auth.login_brand_heading');
    @endphp
    <h1>{!! nl2br(e($heading)) !!}</h1>
    <p class="sub">{{ ___('auth.login_brand_subtitle') }}</p>
@endsection

@section('form')
    <div class="top-link">
        {{ ___('auth.login_top_text') }}
        <a href="{{ route('registerForm') }}">{{ ___('auth.login_top_link') }}</a>
    </div>

    <h2>{{ ___('auth.login_form_title') }}</h2>
    <p class="lead-sm">{{ ___('auth.login_form_intro') }}</p>

    <form action="{{ route('login') }}" method="POST" data-demo>
        @csrf

        <div class="form-group">
            <label class="form-label">{{ ___('label.Email') }}</label>
            <input type="email" name="email" class="form-control"
                   value="{{ Cookie::get('email', old('email')) }}"
                   placeholder="{{ ___('placeholder.enter_email') }}" autofocus>
            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="row-between mb-1">
                <label class="form-label mb-0">{{ ___('label.Password') }}</label>
                <a class="forgot" href="{{ route('password.forgotForm') }}">{{ ___('label.Forgot Password') }}?</a>
            </div>
            <div class="input-wrap">
                <input type="password" name="password" id="pwd" class="form-control"
                       placeholder="{{ ___('placeholder.enter_password') }}">
                <span class="toggle" onclick="var p=document.getElementById('pwd');p.type=p.type==='password'?'text':'password';this.textContent=p.type==='password'?'{{ ___('frontend.show') }}':'{{ ___('frontend.hide') }}';">{{ ___('frontend.show') }}</span>
            </div>
            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="row-between mb-4 mt-3">
            <label class="remember mb-0">
                <input type="checkbox" name="remember" @checked(old('remember', Cookie::has('useremail'))) class="mr-1">
                {{ ___('label.Remember Me') }}
            </label>
        </div>

        <button type="submit" class="btn-blue">{{ ___('auth.login_submit_button') }}</button>
    </form>

    @php
        $googleSocialLoginReady = (int) settings('google_status') === \App\Enums\Status::ACTIVE->value
            && filled(settings('google_client_id')) && filled(settings('google_client_secret'));
        $facebookSocialLoginReady = (int) settings('facebook_status') === \App\Enums\Status::ACTIVE->value
            && filled(settings('facebook_client_id')) && filled(settings('facebook_client_secret'));
    @endphp
    @if($googleSocialLoginReady || $facebookSocialLoginReady)
        <div class="social-login-buttons mt-3">
            @if($googleSocialLoginReady)
                <a href="{{ route('social.login.redirect', 'google') }}" class="btn btn-block mb-2 social-login-btn social-login-btn--google"><span class="social-login-icon" aria-hidden="true"><svg viewBox="0 0 24 24" role="img" aria-label="Google"><path fill="#4285F4" d="M21.35 12.27c0-.72-.06-1.42-.18-2.09H12v3.95h5.24a4.48 4.48 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.91-4.18 2.91-7.25Z"/><path fill="#34A853" d="M12 21.6c2.63 0 4.84-.87 6.45-2.36l-3.14-2.45c-.87.58-1.98.92-3.31.92-2.54 0-4.69-1.72-5.46-4.03H3.3v2.53A9.74 9.74 0 0 0 12 21.6Z"/><path fill="#FBBC05" d="M6.54 13.68a5.86 5.86 0 0 1 0-3.36V7.79H3.3a9.6 9.6 0 0 0 0 8.42l3.24-2.53Z"/><path fill="#EA4335" d="M12 6.29c1.43 0 2.71.49 3.72 1.45l2.79-2.79C16.84 3.34 14.63 2.4 12 2.4a9.74 9.74 0 0 0-8.7 5.39l3.24 2.53C7.31 8.01 9.46 6.29 12 6.29Z"/></svg></span>{{ ___('auth.login_continue_with_google') }}</a>
            @endif
            @if($facebookSocialLoginReady)
                <a href="{{ route('social.login.redirect', 'facebook') }}" class="btn btn-block social-login-btn social-login-btn--facebook"><span class="social-login-icon" aria-hidden="true"><i class="fa-brands fa-facebook-f"></i></span>{{ ___('auth.login_continue_with_facebook') }}</a>
            @endif
        </div>
    @endif

    @include('auth.partials.demo-logins', ['portalOnly' => false])

    <div class="divider">{{ ___('auth.divider_text') }}</div>

    <p class="signup-line mb-0">
        {{ ___('auth.login_signup_text') }}
        <a href="{{ route('registerForm') }}">{{ ___('auth.login_signup_link') }}</a>
    </p>

    <div class="admin-hint">
        <a href="{{ route('admin.loginForm') }}">{{ ___('auth.login_admin_link') }}</a>
    </div>
@endsection
