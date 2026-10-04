{{-- Shared transport-service form fields.
     Expects: $item (null on create), $statuses, $icons, $bookingTypes. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">Service title <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="Airport Transfer" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="icon">Icon</label>
        <select id="icon" name="icon" class="form-control input-style-1 select2">
            <option value="">Default (van)</option>
            @foreach($icons as $class => $label)
                <option value="{{ $class }}" @selected(old('icon', $item->icon ?? '') === $class)>{{ $label }}</option>
            @endforeach
        </select>
        @error('icon') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="price_from">Price from ({{ currency_symbol() }})</label>
        <input type="number" step="0.01" min="0" id="price_from" name="price_from" class="form-control input-style-1" value="{{ old('price_from', $item->price_from ?? 0) }}">
        @error('price_from') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="price_unit">Price unit</label>
        <input type="text" id="price_unit" name="price_unit" class="form-control input-style-1" placeholder="/day" value="{{ old('price_unit', $item->price_unit ?? '') }}">
        @error('price_unit') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="booking_type">“Book now” goes to <span class="text-danger">*</span></label>
        <select id="booking_type" name="booking_type" class="form-control input-style-1 select2">
            @foreach($bookingTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('booking_type', $item->booking_type ?? 'car-rental') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('booking_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="sort_order">Sort order</label>
        <input type="number" min="0" id="sort_order" name="sort_order" class="form-control input-style-1" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
        @error('sort_order') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="form-control input-style-1" placeholder="Meet-and-greet pickup & drop in a private car.">{{ old('description', $item->description ?? '') }}</textarea>
        @error('description') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.transport-service.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
