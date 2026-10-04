{{-- Shared Hajj Pilgrim form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $hajjPackages, $customers, $paymentStatuses, $documentStatuses, $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="pilgrim_no">{{ ___('label.pilgrim_no') }}</label>
        <input type="text" id="pilgrim_no" name="pilgrim_no" class="form-control input-style-1" placeholder="PIL-1001" value="{{ old('pilgrim_no', $item->pilgrim_no ?? '') }}">
        <small class="text-muted">Leave blank to generate the next number automatically.</small>
        @error('pilgrim_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="name">{{ ___('label.name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" placeholder="{{ ___('label.name') }}" value="{{ old('name', $item->name ?? '') }}">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="passport_no">{{ ___('label.passport_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="passport_no" name="passport_no" class="form-control input-style-1" placeholder="{{ ___('label.passport_no') }}" value="{{ old('passport_no', $item->passport_no ?? '') }}">
        @error('passport_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="hajj_package_id">{{ ___('label.hajj_package') }} <span class="text-danger">*</span></label>
        <select id="hajj_package_id" name="hajj_package_id" class="form-control input-style-1 select2" placeholder="{{ ___('label.hajj_package') }}">
            <option value=""></option>
            @foreach($hajjPackages as $package)
                <option value="{{ $package->id }}" @selected(old('hajj_package_id', $item->hajj_package_id ?? '') == $package->id)>{{ $package->title }}</option>
            @endforeach
        </select>
        @error('hajj_package_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="group_name">{{ ___('label.group_name') }}</label>
        <select id="group_name" name="group_name" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.unassigned') }}</option>
            @foreach($groupNames as $name)
                <option value="{{ $name }}" @selected(old('group_name', $item->group_name ?? '') === $name)>{{ $name }}</option>
            @endforeach
        </select>
        @error('group_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

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

    {{-- Derived from amount_paid / amount_due; record money on the Payments page. --}}
    <div class="form-group col-md-4">
        <label class="label-style-1">{{ ___('label.payment_status') }}</label>
        <div>
            @if($item)
                <span class="bullet-badge bullet-badge-{{ $item->paymentTone() }}">{{ $item->payment_status }}</span>
                <small class="text-muted d-block mt-1">{{ currency_symbol() }}{{ number_format($item->amount_paid, 2) }} paid · {{ currency_symbol() }}{{ number_format($item->amount_due, 2) }} due — record payments on the <a href="{{ route('hajj.payments') }}">Payments</a> page.</small>
            @else
                <span class="bullet-badge bullet-badge-danger">Pending</span>
                <small class="text-muted d-block mt-1">Set automatically from the package price and recorded payments.</small>
            @endif
        </div>
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="document_status">{{ ___('label.document_status') }} <span class="text-danger">*</span></label>
        <select id="document_status" name="document_status" class="form-control input-style-1 select2">
            @foreach($documentStatuses as $ds)
                <option value="{{ $ds }}" @selected(old('document_status', $item->document_status ?? '') === $ds)>{{ $ds }}</option>
            @endforeach
        </select>
        @error('document_status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'Registered') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('hajj.pilgrim.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
