{{-- Shared flight-route form fields. Expects: $item (null on create), $statuses, $tripTypes. --}}
<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="origin">Origin <span class="text-danger">*</span></label>
        <input type="text" id="origin" name="origin" class="form-control input-style-1" placeholder="Dhaka" value="{{ old('origin', $item->origin ?? '') }}">
        @error('origin') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-2">
        <label class="label-style-1" for="origin_code">Origin code</label>
        <input type="text" id="origin_code" name="origin_code" class="form-control input-style-1" placeholder="DAC" value="{{ old('origin_code', $item->origin_code ?? '') }}">
        @error('origin_code') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="destination">Destination <span class="text-danger">*</span></label>
        <input type="text" id="destination" name="destination" class="form-control input-style-1" placeholder="Dubai" value="{{ old('destination', $item->destination ?? '') }}">
        @error('destination') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-2">
        <label class="label-style-1" for="destination_code">Dest. code</label>
        <input type="text" id="destination_code" name="destination_code" class="form-control input-style-1" placeholder="DXB" value="{{ old('destination_code', $item->destination_code ?? '') }}">
        @error('destination_code') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="airline">Airline</label>
        <input type="text" id="airline" name="airline" class="form-control input-style-1" placeholder="Emirates" value="{{ old('airline', $item->airline ?? '') }}">
        @error('airline') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="fare">Fare from ({{ currency_symbol() }})</label>
        <input type="number" step="0.01" min="0" id="fare" name="fare" class="form-control input-style-1" value="{{ old('fare', $item->fare ?? 0) }}">
        @error('fare') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="trip_type">Trip type <span class="text-danger">*</span></label>
        <select id="trip_type" name="trip_type" class="form-control input-style-1 select2">
            @foreach($tripTypes as $t)
                <option value="{{ $t }}" @selected(old('trip_type', $item->trip_type ?? 'One-way') === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('trip_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="sort_order">Sort order</label>
        <input type="number" min="0" id="sort_order" name="sort_order" class="form-control input-style-1" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
        @error('sort_order') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="is_featured">Featured</label>
        <select id="is_featured" name="is_featured" class="form-control input-style-1 select2">
            <option value="0" @selected(! old('is_featured', $item->is_featured ?? false))>No</option>
            <option value="1" @selected((bool) old('is_featured', $item->is_featured ?? false))>Yes</option>
        </select>
        @error('is_featured') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.flight-route.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
