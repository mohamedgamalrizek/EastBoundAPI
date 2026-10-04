@extends('backend.partials.master')
@section('title')
{{ ___('menus.general_settings') }}
@endsection

@section('maincontent')
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">{{ ___('menus.settings') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('settings.general.index') }}" class="breadcrumb-link active">{{ ___('menus.general_settings') }}</a></li>
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
                    <h4 class="title-site">{{ ___('menus.general_settings') }}</h4>
                    <x-how-it-works />
                </div>
                <div class="tv-card-body">

                    <div class="settings-index">
                        <a href="#basic-settings">Basic Agency</a>
                        <a href="#localization-settings">Defaults</a>
                        <a href="#booking-policy-settings">Booking Policy</a>
                        <a href="#public-contact-settings">Public Contact</a>
                        <a href="#asset-settings">Logos & Favicon</a>
                    </div>

                    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="settings-form-row">
                            <section class="settings-section" id="basic-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Basic Agency</h5>
                                        <p>Main business identity used across the admin panel and public website.</p>
                                    </div>
                                    <span class="settings-section__tag">Core</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="name">{{ ___('label.name') }}</label>
                                        <input id="name" type="text" name="name" placeholder="{{ ___('placeholder.enter_name') }}" class="form-control input-style-1" value="{{ old('name', settings('name')) }}">
                                        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="phone">{{ ___('label.phone') }}</label>
                                        <input id="phone" type="text" name="phone" placeholder="{{ ___('placeholder.enter_phone') }}" class="form-control input-style-1" value="{{ old('phone', settings('phone')) }}">
                                        @error('phone') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="email">{{ ___('label.email') }}</label>
                                        <input id="email" type="text" name="email" placeholder="{{ ___('placeholder.enter_email') }}" class="form-control input-style-1" value="{{ old('email', settings('email')) }}">
                                        @error('email') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label class="label-style-1" for="copyright">{{ ___('label.copyright') }}</label>
                                        <input id="copyright" type="text" name="copyright" placeholder="{{ ___('placeholder.enter_copyright') }}" class="form-control input-style-1" value="{{ old('copyright', settings('copyright')) }}">
                                        @error('copyright') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </section>


                            <section class="settings-section" id="localization-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Defaults</h5>
                                        <p>System-wide display defaults for lists, dates, time, language, and currency.</p>
                                    </div>
                                    <span class="settings-section__tag">System</span>
                                </div>
                                <div class="form-row">
                                    {{-- Tax rates the Accounting > Tax Reports page applies. Kept
                                         here rather than hard-coded so a rate change is a settings
                                         edit, not a code change. --}}
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="vat_rate">VAT rate (%)</label>
                                        <input id="vat_rate" type="number" step="0.01" min="0" name="vat_rate" placeholder="15" class="form-control input-style-1" value="{{ old('vat_rate', settings('vat_rate')) }}">
                                        @error('vat_rate') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="ait_rate">AIT rate (%)</label>
                                        <input id="ait_rate" type="number" step="0.01" min="0" name="ait_rate" placeholder="5" class="form-control input-style-1" value="{{ old('ait_rate', settings('ait_rate')) }}">
                                        @error('ait_rate') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="paginate_value">{{ ___('label.paginate_value') }}</label>
                                        <input id="paginate_value" type="number" name="paginate_value" placeholder="{{ ___('placeholder.paginate_value') }}" class="form-control input-style-1" value="{{ old('paginate_value', settings('paginate_value')) }}">
                                        @error('paginate_value') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="date_format">{{ ___('label.date_format') }}</label>
                                        <select id="date_format" class="form-control input-style-1 select2" name="date_format">
                                            @foreach(config('site.date_format') as $format)
                                                <option value="{{ $format }}" @selected(old('date_format', settings('date_format')) == $format)>{{ today()->format($format) }}</option>
                                            @endforeach
                                        </select>
                                        @error('date_format') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="time_format">{{ ___('label.time_format') }}</label>
                                        <select id="time_format" class="form-control input-style-1 select2" name="time_format">
                                            @foreach(config('site.time_format') as $format)
                                                <option value="{{ $format }}" @selected(old('time_format', settings('time_format')) == $format)>{{ now()->format($format) }}</option>
                                            @endforeach
                                        </select>
                                        @error('time_format') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="currency_code">{{ ___('label.currency') }}</label>
                                        <select class="form-control input-style-1 select2" id="currency_code" name="currency_code" required>
                                            <option></option>
                                            @foreach ($currencies ?? [] as $currency)
                                                <option value="{{ $currency->code }}" @selected(old('currency_code', settings('currency_code')) == $currency->code)>{{ $currency->name . ' - ' . $currency->symbol }}</option>
                                            @endforeach
                                        </select>
                                        @error('currency_code') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="language">{{ ___('label.default language') }}</label>
                                        <select name="language" id="language" class="form-control input-style-1 select2">
                                            <option></option>
                                            @foreach($languages as $row)
                                                <option value="{{ $row->code }}" @selected(old('language', settings('language')) == $row->code)>{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('language') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="settings-section" id="booking-policy-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Booking Policy</h5>
                                        <p>Controls when a customer may cancel their own paid tour booking, and what the agency keeps if they do.</p>
                                    </div>
                                    <span class="settings-section__tag">Bookings</span>
                                </div>
                                <div class="form-row">
                                    {{-- A customer-initiated cancel (mobile app / customer portal) is
                                         refused once the booking's travel date is inside this window,
                                         or already past. Back-office cancellation is unaffected. --}}
                                    <div class="form-group col-md-6">
                                        <label class="label-style-1" for="booking_cancellation_window_hours">Cancellation window (hours)</label>
                                        <input id="booking_cancellation_window_hours" type="number" step="1" min="0" name="booking_cancellation_window_hours" placeholder="24" class="form-control input-style-1" value="{{ old('booking_cancellation_window_hours', settings('booking_cancellation_window_hours')) }}">
                                        <small class="text-muted d-block mt-2">A customer can only cancel a booking online if the travel date is at least this many hours away.</small>
                                        @error('booking_cancellation_window_hours') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="label-style-1" for="booking_cancellation_penalty_percent">Cancellation penalty (%)</label>
                                        <input id="booking_cancellation_penalty_percent" type="number" step="1" min="0" max="100" name="booking_cancellation_penalty_percent" placeholder="10" class="form-control input-style-1" value="{{ old('booking_cancellation_penalty_percent', settings('booking_cancellation_penalty_percent')) }}">
                                        <small class="text-muted d-block mt-2">Percentage of the paid amount the agency keeps when a customer cancels a paid booking; the rest is refunded to their wallet.</small>
                                        @error('booking_cancellation_penalty_percent') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="settings-section" id="public-contact-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Public Contact</h5>
                                        <p>Contact information shown in the website header, footer, and contact page.</p>
                                    </div>
                                    <span class="settings-section__tag">Website</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="phone_secondary">Secondary phone</label>
                                        <input id="phone_secondary" type="text" name="phone_secondary" placeholder="+880 2 5500-0000" class="form-control input-style-1" value="{{ old('phone_secondary', settings('phone_secondary')) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="whatsapp">WhatsApp number</label>
                                        <input id="whatsapp" type="text" name="whatsapp" placeholder="+880 1700-000000" class="form-control input-style-1" value="{{ old('whatsapp', settings('whatsapp')) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="address_short">Short address <small class="text-muted">(top bar)</small></label>
                                        <input id="address_short" type="text" name="address_short" placeholder="Gulshan, Dhaka, Bangladesh" class="form-control input-style-1" value="{{ old('address_short', settings('address_short')) }}">
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label class="label-style-1" for="address">Full address <small class="text-muted">(footer and contact page)</small></label>
                                        <input id="address" type="text" name="address" placeholder="House 42, Road 11, Gulshan-1, Dhaka 1212, Bangladesh" class="form-control input-style-1" value="{{ old('address', settings('address')) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="site_tagline">Footer tagline</label>
                                        <input id="site_tagline" type="text" name="site_tagline" placeholder="Your trusted travel partner" class="form-control input-style-1" value="{{ old('site_tagline', settings('site_tagline')) }}">
                                    </div>
                                </div>
                            </section>

                            <section class="settings-section" id="asset-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Logos & Favicon</h5>
                                        <p>Brand assets used in the admin panel, public website, browser tab, and login pages.</p>
                                    </div>
                                    <span class="settings-section__tag">Assets</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1">{{ ___('label.light_theme_logo') }}<span class="fillable"></span></label>
                                        <div class="ot_fileUploader left-side mb-3">
                                            <input class="form-control input-style-1 placeholder" type="text" placeholder="Attach File" readonly>
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="j-td-btn" for="light_theme_logo">{{ ___('label.browse') }}</label>
                                                <input type="file" class="d-none form-control" name="light_theme_logo" id="light_theme_logo" accept="image/jpeg, image/jpg, image/png, image/webp">
                                            </button>
                                        </div>
                                        <div class="col-6 text-center p-1">
                                            <img src="{{ logo(settings('light_theme_logo')) }}" alt="logo" height="50" class="obj-fit-contain">
                                        </div>
                                        <small class="text-muted d-block mt-2">{{ ___('label.light_theme_logo_help') }}</small>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="label-style-1">{{ ___('label.dark_theme_logo') }} <span class="fillable"></span></label>
                                        <div class="ot_fileUploader left-side mb-3">
                                            <input class="form-control input-style-1 placeholder" type="text" placeholder="Attach File" readonly>
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="j-td-btn" for="dark_theme_logo">{{ ___('label.browse') }}</label>
                                                <input type="file" class="d-none form-control" name="dark_theme_logo" id="dark_theme_logo" accept="image/jpeg, image/jpg, image/png, image/webp">
                                            </button>
                                        </div>
                                        <div class="text-center bg-dark p-1">
                                            <img src="{{ logo(settings('dark_theme_logo')) }}" alt="Logo" height="50" class="obj-fit-contain">
                                        </div>
                                        <small class="text-muted d-block mt-2">{{ ___('label.dark_theme_logo_help') }}</small>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="label-style-1">{{ ___('label.favicon') }}<span class="fillable"></span></label>
                                        <div class="ot_fileUploader left-side mb-3">
                                            <input class="form-control input-style-1 placeholder" type="text" placeholder="Image" readonly>
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="j-td-btn" for="fileBrouse2">{{ ___('label.Browse') }}</label>
                                                <input type="file" class="d-none form-control" name="favicon" id="fileBrouse2" accept="image/jpg, image/jpeg, image/png">
                                            </button>
                                        </div>
                                        <div class="text-center">
                                            <img src="{{ favicon(settings('favicon')) }}" alt="favicon" class="rounded mt-3" width="50">
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="j-create-btns mt-4">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
