{{-- Shared content-block form. Expects: $item (null on create), $sections, $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="section">{{ ___('label.where_it_appears') }} <span class="text-danger">*</span></label>
        <select id="section" name="section" class="form-control input-style-1 select2">
            @foreach($sections as $value => $label)
                <option value="{{ $value }}" @selected(old('section', $item->section ?? 'home_services') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('section') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="Visa Services" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="icon">{{ ___('label.icon') }} <small class="text-muted">{{ ___('label.icon_fa_hint') }}</small></label>
        <input type="text" id="icon" name="icon" class="form-control input-style-1" placeholder="fa-passport" value="{{ old('icon', $item->icon ?? '') }}">
        @error('icon') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="url">{{ ___('label.link') }} <small class="text-muted">{{ ___('label.link_hint') }}</small></label>
        <input type="text" id="url" name="url" class="form-control input-style-1" placeholder="front.visa" value="{{ old('url', $item->url ?? '') }}">
        @error('url') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-2">
        <label class="label-style-1" for="sort_order">{{ ___('label.sort_order') }}</label>
        <input type="number" min="0" id="sort_order" name="sort_order" class="form-control input-style-1" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
        @error('sort_order') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-2">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="body">{{ ___('label.description') }}</label>
        <textarea id="body" name="body" rows="3" class="form-control input-style-1" placeholder="{{ ___('label.shown_under_title_hint') }}">{{ old('body', $item->body ?? '') }}</textarea>
        @error('body') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        @include('backend.components.image-field', [
            'name'    => 'image',
            'label'   => ___('label.content_block_image_hint'),
            'current' => $item->image ?? null,
            'folder'  => 'content-blocks',
        ])
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.content-block.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
