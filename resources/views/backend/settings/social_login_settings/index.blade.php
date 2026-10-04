@extends('backend.partials.master')
@section('title')
{{ ___('menus.social_login_settings') }}
@endsection
@section('maincontent')
<div class="container-fluid  dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="breadcrumb-link">{{ ___('menus.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link active">{{ ___('menus.settings') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('settings.social.login.index') }}" class="breadcrumb-link active">{{ ___('menus.social_login_settings') }}</a></li>
                        </ol>
                    </nav>
                </div>
                <x-how-it-works />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <div class="tv-card">
                <div class="tv-card-body">
                    <h4 class="h4 mb-3">{{ ___('label.facebook') }}</h4>
                    @if(hasPermission('general_settings_update'))
                    <form action="{{ route('settings.social.login.update', 'facebook') }}" method="POST" id="facebook-social-login-form">
                        @method('PUT')
                        @csrf
                        @endif
                        <div class="row">
                            <div class="col-12 ">

                                <div class="form-group">
                                    <label class="label-style-1" for="facebook_client_id">{{ ___('label.app_id') }}</label> <span class="text-danger">*</span>
                                    <input id="facebook_client_id" type="text" name="facebook_client_id" data-parsley-trigger="change" placeholder="{{ ___('placeholder.app_id') }}" autocomplete="off" class="form-control input-style-1" value="{{ old('facebook_client_id', settings('facebook_client_id')) }}" require>
                                    @error('facebook_client_id')
                                    <small class="text-danger mt-2">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="label-style-1" for="facebook_client_secret">{{ ___('label.app_secret') }}</label> <span class="text-danger">*</span>
                                    <input id="facebook_client_secret" type="password" name="facebook_client_secret" data-parsley-trigger="change" placeholder="Leave blank to keep the saved secret" autocomplete="new-password" class="form-control input-style-1" value="" >
                                    @error('facebook_client_secret')
                                    <small class="text-danger mt-2">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="label-style-1" for="facebook_client_token">Facebook Client Token <span class="text-muted">(mobile app)</span></label>
                                    <input id="facebook_client_token" type="text" name="facebook_client_token" placeholder="Facebook client token" autocomplete="off" class="form-control input-style-1" value="{{ old('facebook_client_token', settings('facebook_client_token')) }}">
                                    @error('facebook_client_token')
                                    <small class="text-danger mt-2">{{ $message }}</small>
                                    @enderror
                                    <small class="form-text text-muted">Found in Meta Developer Console → Settings → Advanced. This is not the App Secret.</small>
                                </div>

                                <div class="form-group d-flex">
                                    <label class="label-style-1" for="switch-id">{{ ___('label.status') }}</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input switch-id ml-3" name="facebook_status" id="switch-id" type="checkbox" role="switch" @checked(old('facebook_status', settings('facebook_status'))==\App\Enums\Status::ACTIVE->value)>
                                    </div>
                                </div>

                            </div>
                        </div>
                        @if(hasPermission('general_settings_update'))
                        <div class="j-create-btns">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6  col-md-6">
            <div class="tv-card">
                <div class="tv-card-body">
                    <h4 class="h4 mb-3">{{ ___('label.google') }}</h4>
                    @if(hasPermission('general_settings_update'))
                    <form action="{{ route('settings.social.login.update', 'google') }}" method="POST" id="google-social-login-form">
                        @method('PUT')
                        @csrf
                        @endif
                        <div class="row">
                            <div class="col-12 ">
                                <div class="form-group">
                                    <label class="label-style-1" for="google_client_id">{{ ___('label.client_id') }}</label> <span class="text-danger">*</span>
                                    <input id="google_client_id" type="text" name="google_client_id" data-parsley-trigger="change" placeholder="{{ ___('placeholder.client_id') }}" autocomplete="off" class="form-control input-style-1" value="{{ old('google_client_id', settings('google_client_id')) }}" require>
                                    @error('google_client_id')
                                    <small class="text-danger mt-2">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="label-style-1" for="google_client_secret">{{ ___('label.client_secret') }}</label> <span class="text-danger">*</span>
                                    <input id="google_client_secret" type="password" name="google_client_secret" data-parsley-trigger="change" placeholder="Leave blank to keep the saved secret" autocomplete="new-password" class="form-control input-style-1" value="" >
                                    @error('google_client_secret')
                                    <small class="text-danger mt-2">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="label-style-1" for="google_android_client_id">Android client ID <span class="text-muted">(mobile app)</span></label>
                                    <input id="google_android_client_id" type="text" name="google_android_client_id" placeholder="Google OAuth client ID for Android" autocomplete="off" class="form-control input-style-1" value="{{ old('google_android_client_id', settings('google_android_client_id')) }}">
                                    @error('google_android_client_id')
                                    <small class="text-danger mt-2">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="label-style-1" for="google_ios_client_id">iOS client ID <span class="text-muted">(mobile app)</span></label>
                                    <input id="google_ios_client_id" type="text" name="google_ios_client_id" placeholder="Google OAuth client ID for iOS" autocomplete="off" class="form-control input-style-1" value="{{ old('google_ios_client_id', settings('google_ios_client_id')) }}">
                                    @error('google_ios_client_id')
                                    <small class="text-danger mt-2">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group d-flex">
                                    <label class="label-style-1" for="g-switch-id">{{ ___('label.status') }}</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input switch-id ml-3" name="google_status" id="g-switch-id" type="checkbox" role="switch" @checked(old('google_status', settings('google_status'))==\App\Enums\Status::ACTIVE->value)>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(hasPermission('general_settings_update'))
                        <div class="j-create-btns">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection()
