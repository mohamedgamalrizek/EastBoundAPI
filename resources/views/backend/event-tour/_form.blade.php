@php $app = $item ?? null; @endphp
<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">Title <span class=\"text-danger\">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="Title" value="{{ old('title', $app->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="type">Type <span class=\"text-danger\">*</span></label>
        <select id="type" name="type" class="form-control input-style-1 select2" placeholder="Type">
            <option value=""></option>
            @foreach($type_options as $opt)
                <option value="{{ $opt }}" @selected(old('type', $app->type ?? '') == $opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="location">Location</label>
        <input type="text" id="location" name="location" class="form-control input-style-1" placeholder="Location" value="{{ old('location', $app->location ?? '') }}">
        @error('location') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="event_date">Event Date</label>
        <input type="date" id="event_date" name="event_date" class="form-control input-style-1" value="{{ old('event_date', optional($app)->event_date ? \Illuminate\Support\Carbon::parse($app->event_date)->format('Y-m-d') : '') }}">
        @error('event_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="seats">Seats</label>
        <input type="number" id="seats" name="seats" class="form-control input-style-1" placeholder="Seats" value="{{ old('seats', $app->seats ?? '') }}">
        @error('seats') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">Status <span class=\"text-danger\">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2" placeholder="Status">
            <option value=""></option>
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
        <a href="{{ route('event-tour.index') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
    </div>
</div>
