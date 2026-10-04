{{-- Shared job-opening form fields.
     Expects: $item (null on create), $statuses, $employmentTypes. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">Position title <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="Travel Sales Consultant" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="department">Department</label>
        <input type="text" id="department" name="department" class="form-control input-style-1" placeholder="Sales" value="{{ old('department', $item->department ?? '') }}">
        @error('department') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="location">Location</label>
        <input type="text" id="location" name="location" class="form-control input-style-1" placeholder="Dhaka (On-site)" value="{{ old('location', $item->location ?? '') }}">
        @error('location') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="employment_type">Employment type <span class="text-danger">*</span></label>
        <select id="employment_type" name="employment_type" class="form-control input-style-1 select2">
            @foreach($employmentTypes as $t)
                <option value="{{ $t }}" @selected(old('employment_type', $item->employment_type ?? 'Full-time') === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('employment_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="closing_date">Closing date <small class="text-muted">(hidden after this date)</small></label>
        <input type="date" id="closing_date" name="closing_date" class="form-control input-style-1" value="{{ old('closing_date', optional($item?->closing_date)->format('Y-m-d')) }}">
        @error('closing_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-2">
        <label class="label-style-1" for="sort_order">Sort order</label>
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
        <label class="label-style-1" for="description">Description</label>
        <textarea id="description" name="description" rows="5" class="form-control input-style-1" placeholder="Role summary, responsibilities and requirements.">{{ old('description', $item->description ?? '') }}</textarea>
        @error('description') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.job-opening.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
