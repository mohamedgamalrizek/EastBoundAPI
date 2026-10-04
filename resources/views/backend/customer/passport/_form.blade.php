{{-- Shared Passport form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $customers, $statuses. --}}
@php
    $app = $item ?? null;
    $issueDate  = old('issue_date', $app && $app->issue_date ? $app->issue_date->format('Y-m-d') : '');
    $expiryDate = old('expiry_date', $app && $app->expiry_date ? $app->expiry_date->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_id">{{ ___('label.customer') }}</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $app->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="holder_name">{{ ___('label.holder_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="holder_name" name="holder_name" class="form-control input-style-1" placeholder="{{ ___('label.holder_name') }}" value="{{ old('holder_name', $app->holder_name ?? '') }}">
        @error('holder_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="passport_no">{{ ___('label.passport_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="passport_no" name="passport_no" class="form-control input-style-1" placeholder="{{ ___('label.passport_no') }}" value="{{ old('passport_no', $app->passport_no ?? '') }}">
        @error('passport_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="nationality">{{ ___('label.nationality') }}</label>
        <input type="text" id="nationality" name="nationality" class="form-control input-style-1" placeholder="{{ ___('label.nationality') }}" value="{{ old('nationality', $app->nationality ?? '') }}">
        @error('nationality') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="issue_date">{{ ___('label.issue_date') }}</label>
        <input type="date" id="issue_date" name="issue_date" class="form-control input-style-1" value="{{ $issueDate }}">
        @error('issue_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="expiry_date">{{ ___('label.expiry_date') }}</label>
        <input type="date" id="expiry_date" name="expiry_date" class="form-control input-style-1" value="{{ $expiryDate }}">
        @error('expiry_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('customer.passport.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
