@extends('backend.partials.master')
@section('title') {{ ___('label.apply_for_leave') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.apply_for_leave') }}" :breadcrumb="[___('permissions.staff_portal'), ___('label.leave'), ___('label.apply_leave')]">
    <div class="row"><div class="col-lg-8"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('staff.leave.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="label-style-1" for="leave_type">{{ ___('label.leave_type') }} <span class="text-danger">*</span></label>
                    <select id="leave_type" name="leave_type" class="form-control input-style-1 select2">
                        <option value="">{{ ___('label.select') }}</option>
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt }}" @selected(old('leave_type') === $lt)>{{ $lt }}</option>
                        @endforeach
                    </select>
                    @error('leave_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-3">
                    <label class="label-style-1" for="from_date">{{ ___('label.from_date') }} <span class="text-danger">*</span></label>
                    <input type="date" id="from_date" name="from_date" class="form-control input-style-1" value="{{ old('from_date') }}">
                    @error('from_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-3">
                    <label class="label-style-1" for="to_date">{{ ___('label.to_date') }} <span class="text-danger">*</span></label>
                    <input type="date" id="to_date" name="to_date" class="form-control input-style-1" value="{{ old('to_date') }}">
                    @error('to_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-12">
                    <label class="label-style-1" for="reason">{{ ___('label.reason') }}</label>
                    <textarea id="reason" name="reason" rows="3" class="form-control input-style-1">{{ old('reason') }}</textarea>
                    @error('reason') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
            </div>
            <div class="j-create-btns"><div class="drp-btns">
                <button type="submit" class="j-td-btn">{{ ___('label.save') }}</button>
                <a href="{{ route('staff.leave') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
            </div></div>
        </form>
    </div></div></div></div>
</x-page>
@endsection
