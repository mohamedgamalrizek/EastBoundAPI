{{-- Shared Testimonial form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="name">{{ ___('label.name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" placeholder="{{ ___('label.name') }}" value="{{ old('name', $item->name ?? '') }}">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="role">{{ ___('label.role') }}</label>
        <input type="text" id="role" name="role" class="form-control input-style-1" placeholder="{{ ___('label.role') }}" value="{{ old('role', $item->role ?? '') }}">
        @error('role') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        @include('backend.components.image-field', [
            'name'    => 'avatar',
            'label'   => 'Photo (blank shows initials)',
            'current' => $item->avatar ?? null,
            'folder'  => 'testimonials',
        ])
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="city">City</label>
        <input type="text" id="city" name="city" class="form-control input-style-1" placeholder="Dhaka" value="{{ old('city', $item->city ?? '') }}">
        @error('city') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="rating">{{ ___('label.rating') }} <span class="text-danger">*</span></label>
        <input type="number" id="rating" name="rating" min="1" max="5" class="form-control input-style-1" placeholder="5" value="{{ old('rating', $item->rating ?? 5) }}">
        @error('rating') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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

    <div class="form-group col-md-12">
        <label class="label-style-1" for="message">{{ ___('label.message') }}</label>
        <textarea id="message" name="message" rows="4" class="form-control input-style-1" placeholder="{{ ___('label.message') }}">{{ old('message', $item->message ?? '') }}</textarea>
        @error('message') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.testimonial.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
