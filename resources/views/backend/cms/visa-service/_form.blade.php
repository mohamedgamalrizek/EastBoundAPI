{{-- Shared visa-service form fields. Expects: $item (null on create), $statuses, $types. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="country">Country <span class="text-danger">*</span></label>
        <input type="text" id="country" name="country" class="form-control input-style-1" placeholder="United Arab Emirates" value="{{ old('country', $item->country ?? '') }}">
        @error('country') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="flag">Flag emoji</label>
        <input type="text" id="flag" name="flag" class="form-control input-style-1" placeholder="🇦🇪" value="{{ old('flag', $item->flag ?? '') }}">
        @error('flag') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="visa_type">Visa type <span class="text-danger">*</span></label>
        <select id="visa_type" name="visa_type" class="form-control input-style-1 select2">
            @foreach($types as $t)
                <option value="{{ $t }}" @selected(old('visa_type', $item->visa_type ?? 'Tourist') === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('visa_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="processing_time">Processing time <small class="text-muted">(how long the case takes)</small></label>
        <input type="text" id="processing_time" name="processing_time" class="form-control input-style-1" placeholder="3–5 days" value="{{ old('processing_time', $item->processing_time ?? '') }}">
        @error('processing_time') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="stay_duration">Stay duration <small class="text-muted">(how long they may stay)</small></label>
        <input type="text" id="stay_duration" name="stay_duration" class="form-control input-style-1" placeholder="30 days" value="{{ old('stay_duration', $item->stay_duration ?? '') }}">
        @error('stay_duration') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="entry_type">Entry type</label>
        <select id="entry_type" name="entry_type" class="form-control input-style-1 select2">
            @foreach(\App\Models\VisaService::ENTRY_TYPES as $et)
                <option value="{{ $et }}" @selected(old('entry_type', $item->entry_type ?? 'Single') === $et)>{{ $et }} entry</option>
            @endforeach
        </select>
        @error('entry_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- Split so the public page can say what is passed through to the embassy
         and what the agency keeps; `fee` is recalculated from the two. --}}
    <div class="form-group col-md-4">
        <label class="label-style-1" for="govt_fee">Embassy / VFS fee ({{ currency_symbol() }})</label>
        <input type="number" step="0.01" min="0" id="govt_fee" name="govt_fee" class="form-control input-style-1" value="{{ old('govt_fee', $item->govt_fee ?? 0) }}">
        @error('govt_fee') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="service_fee">Our service charge ({{ currency_symbol() }})</label>
        <input type="number" step="0.01" min="0" id="service_fee" name="service_fee" class="form-control input-style-1" value="{{ old('service_fee', $item->service_fee ?? 0) }}">
        @error('service_fee') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1">Total shown to customer</label>
        <input type="text" class="form-control input-style-1" id="visa_fee_total"
               data-total-of="govt_fee,service_fee" data-total-prefix="{{ currency_symbol() }}" readonly>
        <small class="text-muted">Embassy fee + service charge</small>
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="sort_order">Sort order</label>
        <input type="number" min="0" id="sort_order" name="sort_order" class="form-control input-style-1" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
        @error('sort_order') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="is_featured">Featured</label>
        <select id="is_featured" name="is_featured" class="form-control input-style-1 select2">
            <option value="0" @selected(! old('is_featured', $item->is_featured ?? false))>No</option>
            <option value="1" @selected((bool) old('is_featured', $item->is_featured ?? false))>Yes</option>
        </select>
        @error('is_featured') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="requirements">Requirements <small class="text-muted">(one per line)</small></label>
        <textarea id="requirements" name="requirements" rows="5" class="form-control input-style-1" placeholder="Valid passport (6 months)&#10;2 photographs&#10;Bank statement">{{ old('requirements', $item->requirements ?? '') }}</textarea>
        @error('requirements') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.visa-service.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
