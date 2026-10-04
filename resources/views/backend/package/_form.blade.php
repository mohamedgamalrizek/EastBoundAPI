@php $package = $package ?? null; @endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control input-style-1" placeholder="e.g. Maldives Luxury Escape"
            value="{{ old('title', $package->title ?? '') }}">
        @error('title') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Destination <span class="text-danger">*</span></label>
        <input type="text" name="destination" class="form-control input-style-1" placeholder="e.g. Malé, Maldives"
            value="{{ old('destination', $package->destination ?? '') }}">
        @error('destination') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-4">
        <label>Category <span class="text-danger">*</span></label>
        {{-- Options come from the Package Category CRUD, not a hardcoded list,
             so a category added there shows up here immediately. --}}
        @php $selCat = old('category_id', $package->category_id ?? ''); @endphp
        <select name="category_id" class="form-control input-style-1 select2">
            <option value="">— Select category —</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected((string) $selCat === (string) $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        @error('category_id') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
        @if(hasPermission('tour_create'))
        <small class="d-block pt-2">
            Missing one? <a href="{{ route('tour.category') }}" target="_blank">Manage categories</a>
        </small>
        @endif
    </div>

    {{-- Bookings bill price × traveller count, so every label says per adult
         to stop it being read as a whole-trip figure. --}}
    <div class="form-group col-md-4">
        <label>Price per adult ({{ currency_symbol() }}) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="price" class="form-control input-style-1" placeholder="115000"
            value="{{ old('price', $package->price ?? '') }}">
        @error('price') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-4">
        <label>Price per child ({{ currency_symbol() }}) <small class="text-muted">(optional)</small></label>
        <input type="number" step="0.01" min="0" name="child_price" class="form-control input-style-1" placeholder="69000"
            value="{{ old('child_price', $package->child_price ?? '') }}">
        @error('child_price') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-4">
        <label>Single supplement ({{ currency_symbol() }}) <small class="text-muted">(solo room extra)</small></label>
        <input type="number" step="0.01" min="0" name="single_supplement" class="form-control input-style-1" placeholder="15000"
            value="{{ old('single_supplement', $package->single_supplement ?? '') }}">
        @error('single_supplement') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-2">
        <label>Days <span class="text-danger">*</span></label>
        <input type="number" name="duration_days" class="form-control input-style-1"
            value="{{ old('duration_days', $package->duration_days ?? 1) }}">
        @error('duration_days') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-2">
        <label>Nights <span class="text-danger">*</span></label>
        <input type="number" name="duration_nights" class="form-control input-style-1"
            value="{{ old('duration_nights', $package->duration_nights ?? 0) }}">
        @error('duration_nights') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Status <span class="text-danger">*</span></label>
        @php $selStatus = old('status', $package->status ?? 'active'); @endphp
        <select name="status" class="form-control input-style-1 select2">
            <option value="active" @selected($selStatus === 'active')>Active</option>
            <option value="inactive" @selected($selStatus === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        @include('backend.components.image-field', [
            'name'    => 'image',
            'label'   => 'Package image',
            'current' => $package->image ?? null,
            'folder'  => 'packages',
        ])
    </div>

    <div class="form-group col-md-12">
        <label>Description</label>
        <textarea name="description" rows="4" class="form-control input-style-1" placeholder="Package details…">{{ old('description', $package->description ?? '') }}</textarea>
        @error('description') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    {{-- Rendered as the "What's included / Not included" lists on the public
         package page — one item per line. --}}
    <div class="form-group col-md-6">
        <label>What’s included <small class="text-muted">(one per line)</small></label>
        <textarea name="inclusions" rows="6" class="form-control input-style-1" placeholder="Hotel accommodation&#10;Return air tickets&#10;Airport transfers&#10;Daily breakfast">{{ old('inclusions', $package->inclusions ?? '') }}</textarea>
        @error('inclusions') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Not included <small class="text-muted">(one per line)</small></label>
        <textarea name="exclusions" rows="6" class="form-control input-style-1" placeholder="Visa fees&#10;Personal expenses&#10;Travel insurance">{{ old('exclusions', $package->exclusions ?? '') }}</textarea>
        @error('exclusions') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
</div>

{{-- ============================================================
     Day-by-day itinerary. Rows are re-indexed on submit, so adding and
     removing in any order is safe; a row with no title is ignored.
     ============================================================ --}}
@php
    $itineraryRows = old('itinerary', $package?->itineraries
        ->map(fn ($d) => ['day_number' => $d->day_number, 'title' => $d->title, 'description' => $d->description])
        ->all() ?? []);
@endphp

<hr class="my-4">
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Day-by-day itinerary</h5>
        <small class="text-muted">Shown on the public package page. Leave empty to hide the section.</small>
    </div>
    <button type="button" class="j-td-btn" id="addItineraryDay">+ Add day</button>
</div>

<div id="itineraryRows">
    @foreach($itineraryRows as $i => $row)
    <div class="form-row itinerary-row align-items-start border rounded p-2 mb-2">
        <div class="form-group col-md-1">
            <label>Day</label>
            <input type="number" min="1" name="itinerary[{{ $i }}][day_number]" class="form-control input-style-1" value="{{ $row['day_number'] ?? $i + 1 }}">
        </div>
        <div class="form-group col-md-4">
            <label>Title</label>
            <input type="text" name="itinerary[{{ $i }}][title]" class="form-control input-style-1" placeholder="Arrival &amp; check-in" value="{{ $row['title'] ?? '' }}">
        </div>
        <div class="form-group col-md-6">
            <label>Description</label>
            <textarea name="itinerary[{{ $i }}][description]" rows="2" class="form-control input-style-1" placeholder="What happens on this day.">{{ $row['description'] ?? '' }}</textarea>
        </div>
        <div class="form-group col-md-1 d-flex align-items-end">
            <button type="button" class="j-td-btn btn-red remove-itinerary-row" title="Remove">&times;</button>
        </div>
    </div>
    @endforeach
</div>

@error('itinerary') <p class="pt-2 text-danger">{{ $message }}</p> @enderror

@push('scripts')
<script src="{{ asset('backend/js/custom/pages/package-form.js') }}?v={{ filemtime(public_path('backend/js/custom/pages/package-form.js')) }}"></script>
@endpush
