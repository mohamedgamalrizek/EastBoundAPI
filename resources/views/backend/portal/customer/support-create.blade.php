@extends('backend.partials.master')
@section('title') {{ ___('label.new_support_ticket') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.new_support_ticket') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.support'), ___('label.new')]">
    <div class="row"><div class="col-lg-8"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('cust.support.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label class="label-style-1" for="subject">{{ ___('label.subject') }} <span class="text-danger">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control input-style-1" value="{{ old('subject') }}" placeholder="{{ ___('label.subject') }}">
                    @error('subject') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label class="label-style-1" for="priority">{{ ___('label.priority') }} <span class="text-danger">*</span></label>
                    <select id="priority" name="priority" class="form-control input-style-1 select2">
                        @foreach($priorities as $p)
                            <option value="{{ $p }}" @selected(old('priority','Medium') === $p)>{{ $p }}</option>
                        @endforeach
                    </select>
                    @error('priority') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label class="label-style-1" for="department">{{ ___('label.department') }}</label>
                    <select id="department" name="department" class="form-control input-style-1 select2">
                        <option value="">{{ ___('label.select') }}</option>
                        @foreach($departments as $d)
                            <option value="{{ $d }}" @selected(old('department') === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                    @error('department') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
            </div>
            <div class="j-create-btns"><div class="drp-btns">
                <button type="submit" class="j-td-btn">{{ ___('label.save') }}</button>
                <a href="{{ route('cust.support') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
            </div></div>
        </form>
    </div></div></div></div>
</x-page>
@endsection
