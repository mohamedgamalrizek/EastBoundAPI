@extends('backend.partials.master')

@section('title',___('sms.sms_setting') )

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
                            <li class="breadcrumb-item"><a href="{{route('settings.sms.index')}}" class="breadcrumb-link active">{{ ___('menus.sms') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site"> {{ ___('sms.mim_sms_setting') }} </h4>
                    <x-how-it-works />
                </div>
                <div class="tv-card-body">

                    <form action="{{ route('settings.update') }}" method="post">
                        @csrf
                        @method('put')

                        <div class="form-row">

                            <div class="col-md-12 form-group ">
                                <label class="label-style-1">{{ ___('sms.username') }}</label>
                                <input type="text" placeholder="{{ ___('sms.enter_username') }}" class="form-control input-style-1" name="mim_sms_username" value="{{ old('mim_sms_username',settings('mim_sms_username')) }}" @if(!hasPermission('sms_settings_update')) disabled @endif>
                                @error('mim_sms_username') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-12 form-group ">
                                <label class="label-style-1">{{ ___('sms.api_key') }}</label>
                                <input type="text" placeholder="{{ ___('sms.enter_api_key') }}" class="form-control input-style-1" name="mim_sms_api_key" value="{{ old('mim_sms_api_key',settings('mim_sms_api_key')) }}" @if(!hasPermission('sms_settings_update')) disabled @endif>
                                @error('mim_sms_api_key') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-12 form-group ">
                                <label class="label-style-1">{{ ___('sms.sender_id') }}</label>
                                <input type="text" placeholder="{{ ___('sms.enter_sender_id') }}" class="form-control input-style-1" name="mim_sms_sender_id" value="{{ old('mim_sms_sender_id',settings('mim_sms_sender_id')) }}" @if(!hasPermission('sms_settings_update')) disabled @endif>
                                @error('mim_sms_sender_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-12">
                                <label class="label-style-1" for="mim_sms_status">{{ ___('label.status') }}</label>
                                <select name="mim_sms_status" id="mim_sms_status" class="form-control input-style-1 select2" @disabled(!hasPermission('sms_settings_update'))>

                                    @foreach(config('site.status.default') as $key => $status)
                                    <option value="{{ $key }}" @selected(old('mim_sms_status',@settings('mim_sms_status'))==$key)>{{ ___('label.'.$status) }}</option>
                                    @endforeach

                                </select>
                                @error('mim_sms_status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            @if(hasPermission('sms_settings_update'))
                            <div class="j-create-btns">
                                <div class="drp-btns">
                                    <button type="submit" class="j-td-btn">{{ ___('label.save') }}</button>
                                    <a href="{{ route('dashboard') }}" class="j-td-btn btn-red"> <span>{{ ___('label.cancel') }}</span> </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site"> {{ ___('sms.send_test_sms') }} </h4>
                </div>
                <div class="tv-card-body">

                    @if(!is_null($balance))
                    <p class="mb-3">{{ ___('sms.current_balance') }}: <strong>{{ $balance }}</strong></p>
                    @else
                    <p class="mb-3 text-danger">{{ ___('sms.gateway_disabled_hint') }}</p>
                    @endif

                    <form action="{{ route('settings.testSendSms') }}" method="post">
                        @csrf

                        <div class="form-row">
                            <div class="col-md-12 form-group ">
                                <label class="label-style-1">{{ ___('sms.phone') }}</label>
                                <input type="text" placeholder="{{ ___('sms.enter_phone') }}" class="form-control input-style-1" name="phone" value="{{ old('phone') }}" @if(!hasPermission('sms_settings_update')) disabled @endif>
                                @error('phone') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            @if(hasPermission('sms_settings_update'))
                            <div class="j-create-btns">
                                <div class="drp-btns">
                                    <button type="submit" class="j-td-btn">{{ ___('sms.send') }}</button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>


@endsection
