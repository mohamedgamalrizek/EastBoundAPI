@php $app = $item ?? null; @endphp
<div class="form-row">
    <div class="form-group col-md-4">
        <label class="label-style-1" for="code">Code <span class="text-danger">*</span></label>
        <input type="text" id="code" name="code" class="form-control input-style-1" placeholder="Code" value="{{ old('code', $app->code ?? '') }}">
        @error('code') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="type">Type <span class="text-danger">*</span></label>
        <select id="type" name="type" class="form-control input-style-1 select2">
            @foreach($type_options as $opt)
                <option value="{{ $opt }}" @selected(old('type', $app->type ?? '') == $opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="value">Value <span class="text-danger">*</span></label>
        <input type="number" step="0.01" id="value" name="value" class="form-control input-style-1" placeholder="Value" value="{{ old('value', $app->value ?? '') }}">
        @error('value') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="min_spend">Min Spend</label>
        <input type="number" step="0.01" id="min_spend" name="min_spend" class="form-control input-style-1" placeholder="Min Spend" value="{{ old('min_spend', $app->min_spend ?? '') }}">
        @error('min_spend') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="usage_limit">Usage Limit</label>
        <input type="number" id="usage_limit" name="usage_limit" class="form-control input-style-1" placeholder="Usage Limit" value="{{ old('usage_limit', $app->usage_limit ?? '') }}">
        @error('usage_limit') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="expires_at">Expires At</label>
        <input type="date" id="expires_at" name="expires_at" class="form-control input-style-1" value="{{ old('expires_at', optional($app)->expires_at ? \Illuminate\Support\Carbon::parse($app->expires_at)->format('Y-m-d') : '') }}">
        @error('expires_at') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="form-control input-style-1" placeholder="Description">{{ old('description', $app->description ?? '') }}</textarea>
        @error('description') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">Status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($status_options as $opt)
                <option value="{{ $opt }}" @selected(old('status', $app->status ?? '') == $opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? 'Save Changes' : 'Save' }}</button>
        <a href="{{ route('coupon.index') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
    </div>
</div>
