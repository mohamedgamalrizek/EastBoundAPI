{{-- Shared Payslip form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $users, $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="user_id">{{ ___('label.user') }}</label>
        <select id="user_id" name="user_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $item->user_id ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('user_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="staff_name">{{ ___('label.staff_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="staff_name" name="staff_name" class="form-control input-style-1" placeholder="{{ ___('label.staff_name') }}" value="{{ old('staff_name', $item->staff_name ?? '') }}">
        @error('staff_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="month">{{ ___('label.month') }} <span class="text-danger">*</span></label>
        <input type="text" id="month" name="month" class="form-control input-style-1" placeholder="2026-05" value="{{ old('month', $item->month ?? '') }}">
        @error('month') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="basic">{{ ___('label.basic') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="basic" name="basic" class="form-control input-style-1" placeholder="0.00" value="{{ old('basic', $item->basic ?? '') }}">
        @error('basic') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="allowances">{{ ___('label.allowances') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="allowances" name="allowances" class="form-control input-style-1" placeholder="0.00" value="{{ old('allowances', $item->allowances ?? 0) }}">
        @error('allowances') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="deductions">{{ ___('label.deductions') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="deductions" name="deductions" class="form-control input-style-1" placeholder="0.00" value="{{ old('deductions', $item->deductions ?? 0) }}">
        @error('deductions') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="net_pay">{{ ___('label.net_pay') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="net_pay" name="net_pay" class="form-control input-style-1" placeholder="0.00" value="{{ old('net_pay', $item->net_pay ?? '') }}">
        @error('net_pay') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'Paid') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('hr.payslip.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
