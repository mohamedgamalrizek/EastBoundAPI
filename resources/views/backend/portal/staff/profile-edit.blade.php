@extends('backend.partials.master')
@section('title') {{ ___('label.edit_profile') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.edit_profile') }}" :breadcrumb="[___('permissions.staff_portal'), ___('menus.profile'), ___('label.edit')]">
    <div class="row"><div class="col-lg-8"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('staff.profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="label-style-1" for="name">{{ ___('label.name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control input-style-1" value="{{ old('name', $user->name) }}">
                    @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label class="label-style-1" for="email">{{ ___('label.email') }}</label>
                    <input type="text" id="email" class="form-control input-style-1" value="{{ $user->email }}" readonly>
                </div>
                <div class="form-group col-md-6">
                    <label class="label-style-1" for="phone">{{ ___('label.phone') }}</label>
                    <input type="text" id="phone" name="phone" class="form-control input-style-1" value="{{ old('phone', $user->phone) }}">
                    @error('phone') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label class="label-style-1" for="date_of_birth">{{ ___('label.dob') }}</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control input-style-1" value="{{ old('date_of_birth', $user->date_of_birth ? \Illuminate\Support\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '') }}">
                    @error('date_of_birth') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-12">
                    <label class="label-style-1" for="address">{{ ___('label.address') }}</label>
                    <input type="text" id="address" name="address" class="form-control input-style-1" value="{{ old('address', $user->address) }}">
                    @error('address') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-12">
                    <label class="label-style-1" for="about">{{ ___('label.about') }}</label>
                    <textarea id="about" name="about" rows="3" class="form-control input-style-1">{{ old('about', $user->about) }}</textarea>
                    @error('about') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
            </div>
            <div class="j-create-btns"><div class="drp-btns">
                <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                <a href="{{ route('staff.profile') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
            </div></div>
        </form>
    </div></div></div></div>
</x-page>
@endsection
