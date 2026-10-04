{{-- Shared Traveler form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $customers, $relations, $statuses. --}}
@php
    $dob = old('dob', $item && $item->dob ? $item->dob->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_id">{{ ___('label.customer') }}</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $item->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="name">{{ ___('label.name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" placeholder="{{ ___('label.name') }}" value="{{ old('name', $item->name ?? '') }}">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="relation">{{ ___('label.relation') }} <span class="text-danger">*</span></label>
        <select id="relation" name="relation" class="form-control input-style-1 select2">
            @foreach($relations as $rel)
                <option value="{{ $rel }}" @selected(old('relation', $item->relation ?? '') === $rel)>{{ $rel }}</option>
            @endforeach
        </select>
        @error('relation') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="passport_no">{{ ___('label.passport_no') }}</label>
        <input type="text" id="passport_no" name="passport_no" class="form-control input-style-1" placeholder="{{ ___('label.passport_no') }}" value="{{ old('passport_no', $item->passport_no ?? '') }}">
        @error('passport_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="nationality">{{ ___('label.nationality') }}</label>
        <input type="text" id="nationality" name="nationality" class="form-control input-style-1" placeholder="{{ ___('label.nationality') }}" value="{{ old('nationality', $item->nationality ?? '') }}">
        @error('nationality') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="dob">{{ ___('label.dob') }}</label>
        <input type="date" id="dob" name="dob" class="form-control input-style-1" value="{{ $dob }}">
        @error('dob') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'Active') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('customer.traveler.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
