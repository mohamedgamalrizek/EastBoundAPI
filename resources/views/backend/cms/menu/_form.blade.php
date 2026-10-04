{{-- Shared Menu form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $positions, $statuses, $parents. --}}
<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="Tour Packages" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="url">{{ ___('label.url') }} <small class="text-muted">{{ ___('label.site_path_or_full_url_hint') }}</small></label>
        <input type="text" id="url" name="url" class="form-control input-style-1" placeholder="/tour-packages" value="{{ old('url', $item->url ?? '') }}">
        @error('url') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="parent_id">{{ ___('label.parent') }} <small class="text-muted">{{ ___('label.top_level_hint') }}</small></label>
        {{-- Options carry their region so the list can be narrowed to the
             selected position — picking a footer parent for a header item
             used to be possible, and silently moved the item to the footer. --}}
        <select id="parent_id" name="parent_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.top_level_option') }}</option>
            @foreach($parents as $parent)
                @continue($item && $parent->id === $item->id)
                @continue($item && $parent->parent_id === $item->id)
                <option value="{{ $parent->id }}" data-position="{{ $parent->position }}"
                    @selected((int) old('parent_id', $item->parent_id ?? 0) === $parent->id)>
                    {{ str_repeat('— ', $parent->depth() - 1) }}{{ $parent->title }} ({{ $positions[$parent->position] ?? $parent->position }})
                </option>
            @endforeach
        </select>
        @error('parent_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="position">{{ ___('label.position') }} <span class="text-danger">*</span> <small class="text-muted">{{ ___('label.inherited_when_parent_set_hint') }}</small></label>
        <select id="position" name="position" class="form-control input-style-1 select2">
            @foreach($positions as $value => $label)
                <option value="{{ $value }}" @selected(old('position', $item->position ?? 'header') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('position') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="icon">{{ ___('label.icon') }} <small class="text-muted">{{ ___('label.icon_fa_hint') }}</small></label>
        <input type="text" id="icon" name="icon" class="form-control input-style-1" placeholder="fa-umbrella-beach" value="{{ old('icon', $item->icon ?? '') }}">
        @error('icon') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-2">
        <label class="label-style-1" for="target">{{ ___('label.opens_in') }}</label>
        <select id="target" name="target" class="form-control input-style-1 select2">
            <option value="" @selected(! old('target', $item->target ?? null))>{{ ___('label.same_tab') }}</option>
            <option value="_blank" @selected(old('target', $item->target ?? null) === '_blank')>{{ ___('label.new_tab') }}</option>
        </select>
        @error('target') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-2">
        <label class="label-style-1" for="sort_order">{{ ___('label.sort_order') }} <span class="text-danger">*</span></label>
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

    <div class="col-12">
        <div class="alert alert-info py-2 text-14 mb-0">
            <i class="fa fa-circle-info me-1"></i>
            <b>{{ ___('label.menu_header_label') }}</b> {{ ___('label.menu_header_hint') }}
            <b>{{ ___('label.menu_footer_label') }}</b> {{ ___('label.menu_footer_hint') }}
            <b>{{ ___('label.menu_footer_bottom_bar_label') }}</b> {{ ___('label.menu_footer_bottom_bar_hint') }}
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/pages/cms-menu-form.js') }}?v={{ filemtime(public_path('backend/js/custom/pages/cms-menu-form.js')) }}"></script>
@endpush

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.menu.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
