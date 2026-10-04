{{-- Shared Staff Attendance form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $users, $statuses. --}}
@php
    $app = $item ?? null;
    $date = old('date', $app && $app->date ? $app->date->format('Y-m-d') : '');
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
        <label class="label-style-1" for="date">{{ ___('label.date') }} <span class="text-danger">*</span></label>
        <input type="date" id="date" name="date" class="form-control input-style-1" value="{{ $date }}">
        @error('date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="check_in">{{ ___('label.check_in') }}</label>
        <input type="text" id="check_in" name="check_in" class="form-control input-style-1" placeholder="{{ ___('label.check_in') }}" value="{{ old('check_in', $app->check_in ?? '') }}">
        @error('check_in') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="check_out">{{ ___('label.check_out') }}</label>
        <input type="text" id="check_out" name="check_out" class="form-control input-style-1" placeholder="{{ ___('label.check_out') }}" value="{{ old('check_out', $app->check_out ?? '') }}">
        @error('check_out') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="hours">{{ ___('label.hours') }}</label>
        <input type="number" step="0.01" min="0" id="hours" name="hours" class="form-control input-style-1" placeholder="0.00" value="{{ old('hours', $app->hours ?? '') }}">
        @error('hours') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'Present') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('hr.attendance.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
