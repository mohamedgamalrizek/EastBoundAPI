@extends('auth.saas-layout')
@section('title') {{ ___('menus.registration') }} @endsection
@section('card_class') wide @endsection

{{-- Registration creates a customer account, so the panel speaks to a
     traveller. Without these the page inherited the agency pitch — private
     workspace, 40+ modules, payment gateways — and an agency owner reading it
     would reasonably expect signing up to hand them the ERP. --}}
@section('brand_features'){{ ___('auth.register_brand_features') }}@endsection
@section('brand_quote_text'){{ ___('auth.register_quote_text') }}@endsection
@section('brand_quote_name'){{ ___('auth.register_quote_name') }}@endsection
@section('brand_quote_role'){{ ___('auth.register_quote_role') }}@endsection

@section('brand_heading')
    @php
        $heading = ___('auth.register_brand_heading');
    @endphp
    <h1>{!! nl2br(e($heading)) !!}</h1>
    <p class="sub">{{ ___('auth.register_brand_subtitle') }}</p>
@endsection

@section('form')
    <div class="top-link">
        {{ ___('auth.register_top_text') }}
        <a href="{{ route('login') }}">{{ ___('auth.register_top_link') }}</a>
    </div>

    <h2>{{ ___('auth.register_form_title') }}</h2>
    <p class="lead-sm">{{ ___('auth.register_form_intro') }}</p>

    <form action="{{ route('register') }}" method="post">
        @csrf
        <input type="hidden" name="ref" value="{{ old('ref', request('ref')) }}">

        <div class="form-group">
            <label class="form-label">{{ ___('label.Name') }} <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ ___('placeholder.enter_name') }}" autofocus>
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ ___('label.Email') }} <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="{{ ___('placeholder.enter_email') }}">
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            {{-- Phones are stored E.164 and the column is both unique and a
                 login identifier, so the country has to be captured rather
                 than assumed. Same list the mobile app's picker uses. --}}
            <x-phone-input name="phone" :label="___('label.Phone')" :required="true"
                           placeholder="{{ ___('placeholder.enter_phone') }}" />
        </div>

        <div class="form-group">
            <label class="form-label">{{ ___('label.password') }} <span class="text-danger">*</span></label>
            <div class="input-wrap">
                <input type="password" name="password" id="rpwd" class="form-control" placeholder="{{ ___('placeholder.enter_password') }}">
                <span class="toggle" onclick="var p=document.getElementById('rpwd');p.type=p.type==='password'?'text':'password';this.textContent=p.type==='password'?'{{ ___('frontend.show') }}':'{{ ___('frontend.hide') }}';">{{ ___('frontend.show') }}</span>
            </div>
            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ ___('label.confirm_password') }} <span class="text-danger">*</span></label>
            <input type="password" name="confirm_password" class="form-control" placeholder="{{ ___('placeholder.enter_confirm_password') }}">
            @error('confirm_password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ ___('label.Gender') }} <span class="text-danger">*</span></label>
                <select name="gender" class="form-control">
                    <option value="">-</option>
                    @foreach(App\Enums\Gender::cases() as $gender)
                        <option value="{{ $gender->value }}" @selected(old('gender')==$gender->value)>{{ ___("label.{$gender->name}") }}</option>
                    @endforeach
                </select>
                @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ ___('label.date_of_birth') }} <span class="text-danger">*</span></label>
                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}"
                       min="{{ date('Y-m-d', strtotime('-100 years')) }}" max="{{ date('Y-m-d', strtotime('-10 years')) }}">
                @error('date_of_birth') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>

        <button type="submit" class="btn-blue mt-2">{{ ___('auth.register_submit_button') }}</button>
    </form>

    <div class="divider">{{ ___('auth.divider_text') }}</div>

    <p class="signup-line mb-0">
        {{ ___('auth.register_signin_text') }}
        <a href="{{ route('login') }}">{{ ___('auth.register_signin_link') }}</a>
    </p>
@endsection
