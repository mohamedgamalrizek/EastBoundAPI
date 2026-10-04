{{-- Shared Leave Request form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $users, $leaveTypes, $statuses. --}}
@php
    $app = $item ?? null;
    $fromDate = old('from_date', $app && $app->from_date ? $app->from_date->format('Y-m-d') : '');
    $toDate   = old('to_date', $app && $app->to_date ? $app->to_date->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="user_id">{{ ___('label.user') }}</label>
        <select id="user_id" name="user_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $app->user_id ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('user_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="staff_name">{{ ___('label.staff_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="staff_name" name="staff_name" class="form-control input-style-1" placeholder="{{ ___('label.staff_name') }}" value="{{ old('staff_name', $app->staff_name ?? '') }}">
        @error('staff_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="leave_type">{{ ___('label.leave_type') }} <span class="text-danger">*</span></label>
        <select id="leave_type" name="leave_type" class="form-control input-style-1 select2">
            @foreach($leaveTypes as $lt)
                <option value="{{ $lt }}" @selected(old('leave_type', $app->leave_type ?? '') === $lt)>{{ $lt }}</option>
            @endforeach
        </select>
        @error('leave_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="from_date">{{ ___('label.from_date') }} <span class="text-danger">*</span></label>
        <input type="date" id="from_date" name="from_date" class="form-control input-style-1" value="{{ $fromDate }}">
        @error('from_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="to_date">{{ ___('label.to_date') }} <span class="text-danger">*</span></label>
        <input type="date" id="to_date" name="to_date" class="form-control input-style-1" value="{{ $toDate }}">
        @error('to_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="days">{{ ___('label.days') }} <span class="text-danger">*</span></label>
        <input type="number" id="days" name="days" min="0" class="form-control input-style-1" placeholder="0" value="{{ old('days', $app->days ?? '') }}">
        @error('days') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-8">
        <label class="label-style-1" for="reason">{{ ___('label.reason') }}</label>
        <textarea id="reason" name="reason" rows="3" class="form-control input-style-1" placeholder="{{ ___('label.reason') }}">{{ old('reason', $app->reason ?? '') }}</textarea>
        @error('reason') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'Pending') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('hr.leave.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
