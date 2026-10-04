@extends('backend.partials.master')
@section('title', ___('menus.ai_settings'))
@section('maincontent')
<div class="container-fluid dashboard-content"><div class="tv-card"><div class="card-header"><h4 class="title-site">{{ ___('menus.ai_settings') }}</h4><x-how-it-works /></div><div class="tv-card-body">
    <form action="{{ route('settings.update') }}" method="POST">@csrf @method('PUT')
        <div class="settings-form-row"><section class="settings-section"><div class="settings-section__head"><div><h5>OpenAI Configuration</h5><p>Used by the customer AI Trip Planner. The API key is encrypted; leave it blank to keep the saved key.</p></div><span class="settings-section__tag">AI</span></div>
            <div class="form-row">
                <div class="form-group col-md-4"><label class="label-style-1">OpenAI API key</label><input type="password" class="form-control" name="openai_api_key" placeholder="Leave blank to keep saved key" autocomplete="new-password"></div>
                <div class="form-group col-md-4"><label class="label-style-1">OpenAI model</label><input class="form-control" name="openai_model" value="{{ old('openai_model', settings('openai_model') ?: 'gpt-4o-mini') }}"></div>
                <div class="form-group col-md-4"><label class="label-style-1">OpenAI URL</label><input class="form-control" name="openai_api_url" value="{{ old('openai_api_url', settings('openai_api_url') ?: 'https://api.openai.com/v1') }}"></div>
            </div>
        </section></div>
        @if(hasPermission('general_settings_update'))<div class="j-create-btns mt-4"><div class="drp-btns"><button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button></div></div>@endif
    </form>
</div></div></div>
@endsection
