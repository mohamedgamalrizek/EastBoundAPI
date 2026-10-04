{{-- Shared Hajj Package form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $types, $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="package_no">{{ ___('label.package_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="package_no" name="package_no" class="form-control input-style-1" placeholder="{{ ___('label.package_no') }}" value="{{ old('package_no', $item->package_no ?? '') }}">
        @error('package_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-8">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="{{ ___('label.title') }}" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="type">{{ ___('label.type') }} <span class="text-danger">*</span></label>
        <select id="type" name="type" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($types as $tp)
                <option value="{{ $tp }}" @selected(old('type', $item->type ?? '') === $tp)>{{ $tp }}</option>
            @endforeach
        </select>
        @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="duration_days">{{ ___('label.duration_days') }} <span class="text-danger">*</span></label>
        <input type="number" id="duration_days" name="duration_days" min="1" class="form-control input-style-1" placeholder="0" value="{{ old('duration_days', $item->duration_days ?? '') }}">
        @error('duration_days') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="price">{{ ___('label.price') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="price" name="price" class="form-control input-style-1" placeholder="0.00" value="{{ old('price', $item->price ?? '') }}">
        @error('price') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="seats">{{ ___('label.seats') }} <span class="text-danger">*</span></label>
        <input type="number" id="seats" name="seats" min="0" class="form-control input-style-1" placeholder="0" value="{{ old('seats', $item->seats ?? '') }}">
        @error('seats') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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
        <a href="{{ route('hajj.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
