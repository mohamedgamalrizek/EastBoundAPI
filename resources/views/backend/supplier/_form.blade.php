{{-- Shared Supplier form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $types, $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="name">{{ ___('label.name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" placeholder="{{ ___('label.name') }}" value="{{ old('name', $item->name ?? '') }}">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="type">{{ ___('label.type') }} <span class="text-danger">*</span></label>
        <select id="type" name="type" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($types as $type)
                <option value="{{ $type }}" @selected(old('type', $item->type ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="contact_person">{{ ___('label.contact_person') }}</label>
        <input type="text" id="contact_person" name="contact_person" class="form-control input-style-1" placeholder="{{ ___('label.contact_person') }}" value="{{ old('contact_person', $item->contact_person ?? '') }}">
        @error('contact_person') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="phone">{{ ___('label.phone') }}</label>
        <input type="text" id="phone" name="phone" class="form-control input-style-1" placeholder="{{ ___('label.phone') }}" value="{{ old('phone', $item->phone ?? '') }}">
        @error('phone') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="email">{{ ___('label.email') }}</label>
        <input type="email" id="email" name="email" class="form-control input-style-1" placeholder="{{ ___('label.email') }}" value="{{ old('email', $item->email ?? '') }}">
        @error('email') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="balance">{{ ___('label.balance') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" id="balance" name="balance" class="form-control input-style-1" placeholder="0.00" value="{{ old('balance', $item->balance ?? '0') }}">
        @error('balance') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('supplier.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
