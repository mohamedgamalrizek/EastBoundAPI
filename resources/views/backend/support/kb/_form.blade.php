{{-- Shared Knowledge Base Article form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="{{ ___('label.title') }}" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="category">{{ ___('label.category') }} <span class="text-danger">*</span></label>
        <input type="text" id="category" name="category" class="form-control input-style-1" placeholder="{{ ___('label.category') }}" value="{{ old('category', $item->category ?? '') }}">
        @error('category') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'published') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="excerpt">{{ ___('label.excerpt') }}</label>
        <textarea id="excerpt" name="excerpt" rows="3" class="form-control input-style-1" placeholder="{{ ___('label.excerpt') }}">{{ old('excerpt', $item->excerpt ?? '') }}</textarea>
        @error('excerpt') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('support.kb.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
