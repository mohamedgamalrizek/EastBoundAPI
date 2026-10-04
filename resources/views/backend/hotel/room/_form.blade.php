{{-- Shared Hotel Room form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $hotels, $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="hotel_id">{{ ___('label.hotel') }} <span class="text-danger">*</span></label>
        <select id="hotel_id" name="hotel_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($hotels as $hotel)
                <option value="{{ $hotel->id }}" @selected(old('hotel_id', $item->hotel_id ?? '') == $hotel->id)>{{ $hotel->name }}</option>
            @endforeach
        </select>
        @error('hotel_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="room_type">{{ ___('label.room_type') }} <span class="text-danger">*</span></label>
        <input type="text" id="room_type" name="room_type" class="form-control input-style-1" placeholder="{{ ___('label.room_type') }}" value="{{ old('room_type', $item->room_type ?? '') }}">
        @error('room_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="capacity">{{ ___('label.capacity') }} <span class="text-danger">*</span></label>
        <input type="number" id="capacity" name="capacity" min="1" class="form-control input-style-1" placeholder="1" value="{{ old('capacity', $item->capacity ?? '') }}">
        @error('capacity') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="rate_per_night">{{ ___('label.rate_per_night') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="rate_per_night" name="rate_per_night" class="form-control input-style-1" placeholder="0.00" value="{{ old('rate_per_night', $item->rate_per_night ?? '') }}">
        @error('rate_per_night') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="total_rooms">{{ ___('label.total_rooms') }} <span class="text-danger">*</span></label>
        <input type="number" id="total_rooms" name="total_rooms" min="0" class="form-control input-style-1" placeholder="0" value="{{ old('total_rooms', $item->total_rooms ?? '') }}">
        @error('total_rooms') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="available_rooms">{{ ___('label.available_rooms') }} <span class="text-danger">*</span></label>
        <input type="number" id="available_rooms" name="available_rooms" min="0" class="form-control input-style-1" placeholder="0" value="{{ old('available_rooms', $item->available_rooms ?? '') }}">
        @error('available_rooms') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'available') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('hotel.room.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
