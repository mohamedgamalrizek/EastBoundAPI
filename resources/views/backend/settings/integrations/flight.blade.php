@extends('backend.partials.master')
@section('title', ___('menus.flight_api'))
@section('maincontent')
<div class="container-fluid dashboard-content"><div class="tv-card"><div class="card-header"><h4 class="title-site">{{ ___('menus.flight_api') }}</h4><x-how-it-works /></div><div class="tv-card-body">
    <form action="{{ route('settings.update') }}" method="POST">@csrf @method('PUT')
        <div class="settings-form-row"><section class="settings-section"><div class="settings-section__head"><div><h5>Amadeus Flight API</h5><p>Used for live flight-offer search. Credentials are encrypted; leave secret fields blank to keep saved values.</p></div><span class="settings-section__tag">Flights</span></div>
            <div class="form-row">
                <div class="form-group col-md-4"><label class="label-style-1">Amadeus API key</label><input type="password" class="form-control" name="amadeus_api_key" placeholder="Leave blank to keep saved key" autocomplete="new-password"></div>
                <div class="form-group col-md-4"><label class="label-style-1">Amadeus secret</label><input type="password" class="form-control" name="amadeus_api_secret" placeholder="Leave blank to keep saved secret" autocomplete="new-password"></div>
                <div class="form-group col-md-4"><label class="label-style-1">Amadeus URL</label><input class="form-control" name="amadeus_api_url" value="{{ old('amadeus_api_url', settings('amadeus_api_url') ?: 'https://test.api.amadeus.com') }}"></div>
            </div>
        </section></div>
        @if(hasPermission('general_settings_update'))<div class="j-create-btns mt-4"><div class="drp-btns"><button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button></div></div>@endif
    </form>
</div></div></div>
@endsection
