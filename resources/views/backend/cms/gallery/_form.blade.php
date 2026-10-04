{{-- Shared Gallery form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="{{ ___('label.title') }}" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="image_label">{{ ___('label.image_label') }}</label>
        <input type="text" id="image_label" name="image_label" class="form-control input-style-1" placeholder="{{ ___('label.image_label') }}" value="{{ old('image_label', $item->image_label ?? '') }}">
        @error('image_label') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        @include('backend.components.image-field', [
            'name'    => 'image',
            'label'   => 'Image',
            'current' => $item->image ?? null,
            'folder'  => 'gallery',
        ])
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="category">{{ ___('label.category') }}</label>
        <input type="text" id="category" name="category" class="form-control input-style-1" placeholder="{{ ___('label.category') }}" value="{{ old('category', $item->category ?? '') }}">
        @error('category') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-2">
        <label class="label-style-1" for="sort_order">{{ ___('label.sort_order') }}</label>
        <input type="number" min="0" id="sort_order" name="sort_order" class="form-control input-style-1" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
        @error('sort_order') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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
        <a href="{{ route('cms.gallery.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
