@extends('backend.partials.master')

@section('title', ___('menus.push_notifications'))

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
                            <li class="breadcrumb-item"><a href="{{route('settings.push.index')}}" class="breadcrumb-link active">{{ ___('menus.push_notifications') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site"> {{ ___('menus.push_notifications') }} (Firebase Cloud Messaging) </h4>
                </div>
                <div class="tv-card-body">
                    <form action="{{ route('settings.update') }}" method="post">
                        @csrf
                        @method('put')

                        <div class="form-row">

                            <div class="col-md-12 form-group">
                                <label class="label-style-1">Service account JSON</label>
                                <textarea rows="10" class="form-control input-style-1" name="fcm_service_account"
                                          placeholder='Paste the whole file from Firebase Console → Project settings → Service accounts → "Generate new private key".'
                                          @if(!hasPermission('general_settings_update')) disabled @endif>{{ old('fcm_service_account', settings('fcm_service_account')) }}</textarea>
                                <small class="text-muted">
                                    The project id, client email and private key are read from this JSON.
                                    Notifications the system already writes (bookings, payments, visa updates)
                                    are mirrored to the customer's devices when this is on.
                                </small>
                                @error('fcm_service_account') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-12">
                                <label class="label-style-1" for="fcm_status">{{ ___('label.status') }}</label>
                                <select name="fcm_status" id="fcm_status" class="form-control input-style-1 select2" @disabled(!hasPermission('general_settings_update'))>
                                    @foreach(config('site.status.default') as $key => $status)
                                    <option value="{{ $key }}" @selected(old('fcm_status',@settings('fcm_status'))==$key)>{{ ___('label.'.$status) }}</option>
                                    @endforeach
                                </select>
                                @error('fcm_status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            @if($configured)
                                <div class="col-md-12 form-group">
                                    <span class="badge badge-success">Configured — pushes will be attempted.</span>
                                </div>
                            @elseif(settings('fcm_status'))
                                <div class="col-md-12 form-group">
                                    <span class="badge badge-warning">Switched on, but the JSON is missing or does not parse — nothing will be sent.</span>
                                </div>
                            @endif

                            @if(hasPermission('general_settings_update'))
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
    </div>
</div>

@endsection
