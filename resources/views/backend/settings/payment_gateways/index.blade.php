@extends('backend.partials.master')

@section('title',___('payment.payment_gateway_setting') )

@section('maincontent')

<div class="container-fluid  dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}" class="breadcrumb-link">{{ ___('menus.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link active">{{ ___('menus.settings') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{route('settings.payment.index')}}" class="breadcrumb-link active">{{ ___('menus.payment_gateways') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                {{ ___('payment.live_mode_hint') }}
            </div>
        </div>
    </div>

    <div class="row">

        {{-- bKash --}}
        <div class="col-md-6">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site"> {{ ___('payment.bkash_setting') }}
                        @if($status['bkash'])
                            <span class="badge bg-success">{{ ___('label.active') }}</span>
                        @endif
                    </h4>
                    <x-how-it-works />
                </div>
                <div class="tv-card-body">

                    <form action="{{ route('settings.update') }}" method="post">
                        @csrf
                        @method('put')

                        <div class="form-row">

                            <div class="col-md-12 form-group ">
                                <label class="label-style-1">{{ ___('payment.app_key') }}</label>
                                <input type="text" class="form-control input-style-1" name="bkash_app_key" value="{{ old('bkash_app_key',settings('bkash_app_key')) }}" @if(!hasPermission('payment_settings_update')) disabled @endif>
                            </div>

                            <div class="col-md-12 form-group ">
                                <label class="label-style-1">{{ ___('payment.app_secret') }}</label>
                                <input type="text" class="form-control input-style-1" name="bkash_app_secret" value="{{ old('bkash_app_secret',settings('bkash_app_secret')) }}" @if(!hasPermission('payment_settings_update')) disabled @endif>
                            </div>

                            <div class="col-md-6 form-group ">
                                <label class="label-style-1">{{ ___('payment.username') }}</label>
                                <input type="text" class="form-control input-style-1" name="bkash_username" value="{{ old('bkash_username',settings('bkash_username')) }}" @if(!hasPermission('payment_settings_update')) disabled @endif>
                            </div>

                            <div class="col-md-6 form-group ">
                                <label class="label-style-1">{{ ___('payment.password') }}</label>
                                <input type="text" class="form-control input-style-1" name="bkash_password" value="{{ old('bkash_password',settings('bkash_password')) }}" @if(!hasPermission('payment_settings_update')) disabled @endif>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="bkash_sandbox">{{ ___('payment.sandbox') }}</label>
                                <select name="bkash_sandbox" id="bkash_sandbox" class="form-control input-style-1 select2" @disabled(!hasPermission('payment_settings_update'))>
                                    <option value="1" @selected(old('bkash_sandbox',@settings('bkash_sandbox'))=='1')>{{ ___('payment.sandbox_on') }}</option>
                                    <option value="0" @selected(old('bkash_sandbox',@settings('bkash_sandbox'))=='0')>{{ ___('payment.sandbox_off') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="bkash_status">{{ ___('label.status') }}</label>
                                <select name="bkash_status" id="bkash_status" class="form-control input-style-1 select2" @disabled(!hasPermission('payment_settings_update'))>
                                    @foreach(config('site.status.default') as $key => $st)
                                    <option value="{{ $key }}" @selected(old('bkash_status',@settings('bkash_status'))==$key)>{{ ___('label.'.$st) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if(hasPermission('payment_settings_update'))
                            <div class="j-create-btns">
                                <div class="drp-btns">
                                    <button type="submit" class="j-td-btn">{{ ___('label.save') }}</button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- SSLCOMMERZ --}}
        <div class="col-md-6">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site"> {{ ___('payment.sslcommerz_setting') }}
                        @if($status['sslcommerz'])
                            <span class="badge bg-success">{{ ___('label.active') }}</span>
                        @endif
                    </h4>
                    <x-how-it-works />
                </div>
                <div class="tv-card-body">

                    <form action="{{ route('settings.update') }}" method="post">
                        @csrf
                        @method('put')

                        <div class="form-row">

                            <div class="col-md-12 form-group ">
                                <label class="label-style-1">{{ ___('payment.store_id') }}</label>
                                <input type="text" class="form-control input-style-1" name="sslcommerz_store_id" value="{{ old('sslcommerz_store_id',settings('sslcommerz_store_id')) }}" @if(!hasPermission('payment_settings_update')) disabled @endif>
                            </div>

                            <div class="col-md-12 form-group ">
                                <label class="label-style-1">{{ ___('payment.store_password') }}</label>
                                <input type="text" class="form-control input-style-1" name="sslcommerz_store_passwd" value="{{ old('sslcommerz_store_passwd',settings('sslcommerz_store_passwd')) }}" @if(!hasPermission('payment_settings_update')) disabled @endif>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="sslcommerz_sandbox">{{ ___('payment.sandbox') }}</label>
                                <select name="sslcommerz_sandbox" id="sslcommerz_sandbox" class="form-control input-style-1 select2" @disabled(!hasPermission('payment_settings_update'))>
                                    <option value="1" @selected(old('sslcommerz_sandbox',@settings('sslcommerz_sandbox'))=='1')>{{ ___('payment.sandbox_on') }}</option>
                                    <option value="0" @selected(old('sslcommerz_sandbox',@settings('sslcommerz_sandbox'))=='0')>{{ ___('payment.sandbox_off') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="sslcommerz_status">{{ ___('label.status') }}</label>
                                <select name="sslcommerz_status" id="sslcommerz_status" class="form-control input-style-1 select2" @disabled(!hasPermission('payment_settings_update'))>
                                    @foreach(config('site.status.default') as $key => $st)
                                    <option value="{{ $key }}" @selected(old('sslcommerz_status',@settings('sslcommerz_status'))==$key)>{{ ___('label.'.$st) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if(hasPermission('payment_settings_update'))
                            <div class="j-create-btns">
                                <div class="drp-btns">
                                    <button type="submit" class="j-td-btn">{{ ___('label.save') }}</button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site"> {{ ___('payment.callback_urls') }} </h4>
                </div>
                <div class="tv-card-body">
                    <p>{{ ___('payment.callback_hint') }}</p>
                    <ul>
                        <li><code>{{ route('payment.callback', ['gateway' => 'bkash', 'reference' => 'REFERENCE']) }}</code></li>
                        <li><code>{{ route('payment.callback', ['gateway' => 'sslcommerz', 'reference' => 'REFERENCE']) }}</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
