@extends('backend.profile.master')
@section('title')
{{ ___('menus.profile') }}
@endsection
@section('content')
<div id="update_password">
    <div class="settings-form p-4">
        {{-- <h4 class="text-primary">Account Setting</h4> --}}
        <form action="{{route('profile.password.update')}}" method="POST">
            @csrf
            @method('put')

            <div class="form-row">

                <div class="form-group col-md-12">
                    <label class=" label-style-1" for="old_password">{{ ___('label.old_password') }}</label> <span class="text-danger">*</span>
                    <input type="password" id="old_password" name="old_password" placeholder="{{ ___('placeholder.enter_old_password') }}" class="form-control input-style-1 " value="{{ old('old_password') }}">
                    @error('old_password') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>

                <div class="form-group col-md-12">
                    <label class=" label-style-1" for="new_password">{{ ___('label.new_password') }}</label> <span class="text-danger">*</span>
                    <input type="password" id="new_password" name="new_password" placeholder="{{ ___('placeholder.enter_new_password') }}" class="form-control input-style-1" value="{{ old('new_password') }}">
                    @error('new_password') <small class=" text-danger mt-2">{{ $message }}</small> @enderror
                </div>

                <div class="form-group col-md-12">
                    <label class=" label-style-1" for="confirm_password">{{ ___('label.confirm_password') }}</label> <span class="text-danger">*</span>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="{{ ___('placeholder.enter_confirm_password') }}" class="form-control input-style-1" value="{{ old('confirm_password') }}">
                    @error('confirm_password') <small class=" text-danger mt-2">{{ $message }}</small> @enderror
                </div>

            </div>

            <div class="j-create-btns">
                <div class="drp-btns">
                    <button type="submit" class="j-td-btn">{{ ___('label.save') }}</button>
                    <a href="{{ route('profile') }}" class="j-td-btn btn-red"> <span>{{ ___('label.cancel') }}</span> </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection()



