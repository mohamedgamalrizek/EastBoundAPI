@extends('backend.partials.master')
@section('title', ___('menus.whatsapp_settings'))
@section('maincontent')
<div class="container-fluid dashboard-content"><div class="tv-card"><div class="card-header"><h4 class="title-site">{{ ___('menus.whatsapp_settings') }}</h4><x-how-it-works /></div><div class="tv-card-body">
    <form action="{{ route('settings.update') }}" method="POST">@csrf @method('PUT')
        <div class="settings-form-row"><section class="settings-section"><div class="settings-section__head"><div><h5>WhatsApp Cloud API</h5><p>Configure customer messaging. The access token is encrypted; leave it blank to keep the saved token.</p></div><span class="settings-section__tag">Messaging</span></div>
            <div class="form-row">
                <div class="form-group col-md-4"><label class="label-style-1">Access token</label><input type="password" class="form-control" name="whatsapp_access_token" placeholder="Leave blank to keep saved token" autocomplete="new-password"></div>
                <div class="form-group col-md-4"><label class="label-style-1">Phone number ID</label><input class="form-control" name="whatsapp_phone_number_id" value="{{ old('whatsapp_phone_number_id', settings('whatsapp_phone_number_id')) }}"></div>
                <div class="form-group col-md-4"><label class="label-style-1">API version</label><input class="form-control" name="whatsapp_api_version" value="{{ old('whatsapp_api_version', settings('whatsapp_api_version') ?: 'v21.0') }}"></div>
            </div>
        </section></div>
        @if(hasPermission('general_settings_update'))<div class="j-create-btns mt-4"><div class="drp-btns"><button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button></div></div>@endif
    </form>
</div></div></div>
@endsection
