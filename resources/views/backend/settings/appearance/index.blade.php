@extends('backend.partials.master')

@section('title', ___('menus.appearance'))

@section('maincontent')
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">{{ ___('menus.cms') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('settings.appearance.index') }}" class="breadcrumb-link active">{{ ___('menus.appearance') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site">{{ ___('menus.appearance') }}</h4>
                    <x-how-it-works />
                </div>
                <div class="tv-card-body">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="settings-form-row">
                            <section class="settings-section">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Frontend Appearance</h5>
                                        <p>Change the public website design without editing code. These values are saved in system settings.</p>
                                    </div>
                                    <span class="settings-section__tag">Design</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3"><label class="label-style-1">Primary color</label><input type="color" class="form-control" name="frontend_primary_color" value="{{ old('frontend_primary_color', settings('frontend_primary_color') ?: '#A81A20') }}"></div>
                                    <div class="form-group col-md-3"><label class="label-style-1">Secondary color</label><input type="color" class="form-control" name="frontend_secondary_color" value="{{ old('frontend_secondary_color', settings('frontend_secondary_color') ?: '#F7E7E8') }}"></div>
                                    <div class="form-group col-md-3"><label class="label-style-1">Font family</label><select class="form-control" name="frontend_font_family">@foreach(['Inter','Josefin Sans','Sora'] as $font)<option value="{{ $font }}" @selected(old('frontend_font_family', settings('frontend_font_family') ?: 'Inter') === $font)>{{ $font }}</option>@endforeach</select></div>
                                    <div class="form-group col-md-3"><label class="label-style-1">Button radius (px)</label><input type="number" min="0" max="999" class="form-control" name="frontend_button_radius" value="{{ old('frontend_button_radius', settings('frontend_button_radius') ?? 999) }}"></div>
                                    <div class="form-group col-md-3"><label class="label-style-1">Card radius (px)</label><input type="number" min="0" max="50" class="form-control" name="frontend_card_radius" value="{{ old('frontend_card_radius', settings('frontend_card_radius') ?? 20) }}"></div>
                                    <div class="form-group col-md-3"><label class="label-style-1">Form radius (px)</label><input type="number" min="0" max="50" class="form-control" name="frontend_input_radius" value="{{ old('frontend_input_radius', settings('frontend_input_radius') ?? 8) }}"></div>
                                    <div class="form-group col-md-3"><label class="label-style-1">Form border width</label><input type="number" min="0" max="5" class="form-control" name="frontend_input_border_width" value="{{ old('frontend_input_border_width', settings('frontend_input_border_width') ?? 1) }}"></div>
                                    <div class="form-group col-md-3"><label class="label-style-1">Form border style</label><select class="form-control" name="frontend_input_border_style">@foreach(['solid','dashed','dotted'] as $style)<option value="{{ $style }}" @selected(old('frontend_input_border_style', settings('frontend_input_border_style') ?: 'solid') === $style)>{{ ucfirst($style) }}</option>@endforeach</select></div>
                                </div>
                            </section>
                        </div>

                        @if(hasPermission('general_settings_update'))
                        <div class="j-create-btns mt-4">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
