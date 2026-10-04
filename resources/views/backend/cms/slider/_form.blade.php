{{-- Shared Slider form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="{{ ___('label.title') }}" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="subtitle">{{ ___('label.subtitle') }}</label>
        <input type="text" id="subtitle" name="subtitle" class="form-control input-style-1" placeholder="{{ ___('label.subtitle') }}" value="{{ old('subtitle', $item->subtitle ?? '') }}">
        @error('subtitle') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="image_label">{{ ___('label.image_label') }}</label>
        <input type="text" id="image_label" name="image_label" class="form-control input-style-1" placeholder="{{ ___('label.image_label') }}" value="{{ old('image_label', $item->image_label ?? '') }}">
        @error('image_label') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        @include('backend.components.image-field', [
            'name'    => 'image',
            'label'   => 'Hero image',
            'current' => $item->image ?? null,
            'folder'  => 'sliders',
        ])
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="badge">Badge text <small class="text-muted">(pill above the headline)</small></label>
        <input type="text" id="badge" name="badge" class="form-control input-style-1" placeholder="Bangladesh's trusted travel partner" value="{{ old('badge', $item->badge ?? '') }}">
        @error('badge') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="cta_text">Button text</label>
        <input type="text" id="cta_text" name="cta_text" class="form-control input-style-1" placeholder="Browse packages" value="{{ old('cta_text', $item->cta_text ?? '') }}">
        @error('cta_text') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="cta_link">Button link</label>
        <input type="text" id="cta_link" name="cta_link" class="form-control input-style-1" placeholder="/tour-packages" value="{{ old('cta_link', $item->cta_link ?? '') }}">
        @error('cta_link') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="sort_order">{{ ___('label.sort_order') }} <span class="text-danger">*</span> <small class="text-muted">(lowest = hero)</small></label>
        <input type="number" id="sort_order" name="sort_order" min="0" class="form-control input-style-1" placeholder="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
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
        <a href="{{ route('cms.slider.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
