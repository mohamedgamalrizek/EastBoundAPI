<div class="form-row">
    <div class="form-group col-md-4">
        <label class="label-style-1" for="name">Name <span class="text-danger">*</span></label>
        <input id="name" name="name" class="form-control input-style-1" placeholder="Facebook" value="{{ old('name', $item->name ?? '') }}">
        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="form-group col-md-4">
        <label class="label-style-1" for="icon">Font Awesome icon <span class="text-danger">*</span></label>
        <input id="icon" name="icon" class="form-control input-style-1" placeholder="fa-facebook-f" value="{{ old('icon', $item->icon ?? '') }}">
        <small class="text-muted">Use a brand icon class such as fa-instagram.</small>
        @error('icon')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
    <div class="form-group col-md-4">
        <label class="label-style-1" for="sort_order">Sort order</label>
        <input id="sort_order" type="number" min="0" name="sort_order" class="form-control input-style-1" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
    </div>
    <div class="form-group col-md-8">
        <label class="label-style-1" for="url">Profile URL <span class="text-danger">*</span></label>
        <input id="url" type="url" name="url" class="form-control input-style-1" placeholder="https://example.com/your-profile" value="{{ old('url', $item->url ?? '') }}">
        @error('url')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">Status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1">
            <option value="active" @selected(old('status', $item->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $item->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
    </div>
</div>
<div class="j-create-btns"><div class="drp-btns"><button type="submit" class="j-td-btn">{{ $item ? 'Save changes' : 'Save' }}</button><a href="{{ route('settings.social-links.index') }}" class="j-td-btn btn-red">Cancel</a></div></div>
