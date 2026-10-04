@extends('backend.partials.master')
@section('title') {{ ___('menus.preferences') }} @endsection
@section('maincontent')
@php
    $prefs = $user->preferences ?? [];
@endphp
<x-page title="{{ ___('menus.preferences') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.preferences')]">

    <div class="row">
        <div class="col-lg-8">
            <div class="tv-card"><div class="tv-card-body">
                <form action="{{ route('cust.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6>{{ ___('label.account') }}</h6>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="label-style-1" for="name">{{ ___('label.name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control input-style-1"
                                   value="{{ old('name', $user->name) }}">
                            @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label class="label-style-1" for="email">{{ ___('label.email') }}</label>
                            <input type="text" id="email" class="form-control input-style-1"
                                   value="{{ $user->email }}" readonly>
                            <small class="text-muted">{{ ___('label.contact_support_email_hint') }}</small>
                        </div>
                    </div>

                    <h6 class="mt-3">{{ ___('menus.preferences') }}</h6>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="label-style-1" for="language">{{ ___('label.language') }}</label>
                            <select id="language" name="language" class="form-control input-style-1">
                                @foreach(['en' => 'English', 'bn' => 'বাংলা'] as $code => $label)
                                    <option value="{{ $code }}" @selected(old('language', $prefs['language'] ?? 'en') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="label-style-1" for="currency">{{ ___('label.currency') }}</label>
                            <select id="currency" name="currency" class="form-control input-style-1">
                                @foreach(['BDT' => 'BDT (৳)', 'USD' => 'USD ($)'] as $code => $label)
                                    <option value="{{ $code }}" @selected(old('currency', $prefs['currency'] ?? 'BDT') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Label-wrapped checkboxes: the Bootstrap .form-check layout
                         collapsed here, leaving the box sitting on top of its text. --}}
                    <div class="form-group mb-2">
                        <label class="d-inline-flex align-items-center mb-2 tv-click-choice">
                            <input type="hidden" name="email_notifications" value="0">
                            <input type="checkbox" name="email_notifications" value="1"
                                   @checked(old('email_notifications', $prefs['email_notifications'] ?? true))>
                            <span>{{ ___('label.email_notifications') }}</span>
                        </label>
                        <br>
                        <label class="d-inline-flex align-items-center mb-0 tv-click-choice">
                            <input type="hidden" name="sms_alerts" value="0">
                            <input type="checkbox" name="sms_alerts" value="1"
                                   @checked(old('sms_alerts', $prefs['sms_alerts'] ?? true))>
                            <span>{{ ___('label.sms_alerts') }}</span>
                        </label>
                    </div>

                    <div class="j-create-btns"><div class="drp-btns">
                        <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                    </div></div>
                </form>
            </div></div>
        </div>
    </div>

</x-page>
@endsection
