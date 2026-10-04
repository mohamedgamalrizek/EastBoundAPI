@php $app = $item ?? null; @endphp
<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1" for="name">Name <span class=\"text-danger\">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" placeholder="Name" value="{{ old('name', $app->name ?? '') }}">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="channel">Channel <span class=\"text-danger\">*</span></label>
        <select id="channel" name="channel" class="form-control input-style-1 select2">
            @foreach($channel_options as $opt)
                <option value="{{ $opt }}" @selected(old('channel', $app->channel ?? '') == $opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('channel') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="audience">Audience</label>
        <input type="text" id="audience" name="audience" class="form-control input-style-1" placeholder="Audience" value="{{ old('audience', $app->audience ?? '') }}">
        @error('audience') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="budget">Budget</label>
        <input type="number" step=\"0.01\" id="budget" name="budget" class="form-control input-style-1" placeholder="Budget" value="{{ old('budget', $app->budget ?? '') }}">
        @error('budget') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="start_date">Start Date</label>
        <input type="date" id="start_date" name="start_date" class="form-control input-style-1" value="{{ old('start_date', optional($app)->start_date ? \Illuminate\Support\Carbon::parse($app->start_date)->format('Y-m-d') : '') }}">
        @error('start_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="end_date">End Date</label>
        <input type="date" id="end_date" name="end_date" class="form-control input-style-1" value="{{ old('end_date', optional($app)->end_date ? \Illuminate\Support\Carbon::parse($app->end_date)->format('Y-m-d') : '') }}">
        @error('end_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">Status <span class=\"text-danger\">*</span></label>
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
        <a href="{{ route('campaign.index') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
    </div>
</div>
