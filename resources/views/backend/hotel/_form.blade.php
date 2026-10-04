{{-- Shared Hotel form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $statuses, $categories. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="name">{{ ___('label.name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" placeholder="{{ ___('label.hotel') }} {{ ___('label.name') }}" value="{{ old('name', $item->name ?? '') }}">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="city">{{ ___('label.city') }} <span class="text-danger">*</span></label>
        <input type="text" id="city" name="city" class="form-control input-style-1" placeholder="{{ ___('label.city') }}" value="{{ old('city', $item->city ?? '') }}">
        @error('city') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="country">{{ ___('label.country') }} <span class="text-danger">*</span></label>
        <input type="text" id="country" name="country" class="form-control input-style-1" placeholder="{{ ___('label.country') }}" value="{{ old('country', $item->country ?? '') }}">
        @error('country') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="category">{{ ___('label.star_rating') }} <span class="text-danger">*</span></label>
        <select id="category" name="category" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($categories as $star)
                <option value="{{ $star }}" @selected((string) old('category', $item->category ?? '') === (string) $star)>{{ $star }} {{ ___('label.star') }}</option>
            @endforeach
        </select>
        @error('category') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- Public-website fields: shown on /hotel-booking --}}
    <div class="form-group col-md-6">
        @include('backend.components.image-field', [
            'name'    => 'image',
            'label'   => 'Photo (used on the website)',
            'current' => $item->image ?? null,
            'folder'  => 'hotels',
        ])
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="is_featured">Feature on website</label>
        <select id="is_featured" name="is_featured" class="form-control input-style-1 select2">
            <option value="0" @selected(! old('is_featured', $item->is_featured ?? false))>No</option>
            <option value="1" @selected((bool) old('is_featured', $item->is_featured ?? false))>Yes</option>
        </select>
        @error('is_featured') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="description">Website blurb</label>
        <textarea id="description" name="description" rows="2" class="form-control input-style-1" placeholder="Short description shown on the public hotel card.">{{ old('description', $item->description ?? '') }}</textarea>
        @error('description') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="rooms_count">{{ ___('label.rooms_count') }} <span class="text-danger">*</span></label>
        <input type="number" id="rooms_count" name="rooms_count" min="0" class="form-control input-style-1" placeholder="0" value="{{ old('rooms_count', $item->rooms_count ?? '') }}">
        @error('rooms_count') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="price_per_night">{{ ___('label.price_per_night') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="price_per_night" name="price_per_night" class="form-control input-style-1" placeholder="0.00" value="{{ old('price_per_night', $item->price_per_night ?? '') }}">
        @error('price_per_night') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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
        <a href="{{ route('hotel.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
