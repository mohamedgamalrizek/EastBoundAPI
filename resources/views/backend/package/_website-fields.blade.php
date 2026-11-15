@php
    $websiteCountries = \App\Models\WebsiteDestination::orderBy('name')->get();
    $websiteCities = \App\Models\WebsiteCity::with('destination')->orderBy('name')->get();
    $selectedCities = old('website_city_ids', isset($package) ? $package->websiteCities->pluck('id')->all() : []);
@endphp
<fieldset class="mt-4">
    <legend>Website presentation</legend>
    @if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <p class="text-muted">Uses this same tour programme. Website visitors request a quotation; internal prices are not returned by the Website API. Select cities from the selected country only.</p>
    <input type="hidden" name="website_fields" value="1">
    <input type="hidden" name="website_published" value="0"><input type="hidden" name="website_featured" value="0">
    <div class="row">
        <div class="col-md-6 form-group"><label><input type="checkbox" name="website_published" value="1" @checked(old('website_published', $package->website_published ?? false))> Publish on website</label></div>
        <div class="col-md-6 form-group"><label><input type="checkbox" name="website_featured" value="1" @checked(old('website_featured', $package->website_featured ?? false))> Featured tour</label></div>
        <div class="col-md-6 form-group"><label for="website_destination_id">Website country</label><select id="website_destination_id" name="website_destination_id" class="form-control"><option value="">Select country</option>@foreach($websiteCountries as $country)<option value="{{ $country->id }}" @selected(old('website_destination_id', $package->website_destination_id ?? '') == $country->id)>{{ $country->name }}</option>@endforeach</select></div>
        <div class="col-md-6 form-group"><label for="website_city_ids">Cities visited</label><select id="website_city_ids" name="website_city_ids[]" multiple class="form-control select2">@foreach($websiteCities as $city)<option value="{{ $city->id }}" @selected(in_array($city->id, $selectedCities))>{{ $city->destination->name }} — {{ $city->name }}</option>@endforeach</select></div>
        <div class="col-md-6 form-group"><label for="website_type">Tour type</label><select id="website_type" name="website_type" class="form-control">@foreach(['package'=>'Multi-day package','day-tour'=>'Day tour'] as $value=>$label)<option value="{{ $value }}" @selected(old('website_type', $package->website_type ?? 'package') === $value)>{{ $label }}</option>@endforeach</select></div>
        @foreach(['website_slug'=>'URL slug','website_full_title'=>'Full website title','website_tag'=>'Featured label','website_start_point'=>'Day-tour starting point','website_duration_hours'=>'Day-tour duration (hours)'] as $key=>$label)
            <div class="col-md-6 form-group"><label for="{{ $key }}">{{ $label }}</label><input id="{{ $key }}" name="{{ $key }}" class="form-control" value="{{ old($key, $package->$key ?? '') }}">@error($key)<small class="text-danger">{{ $message }}</small>@enderror</div>
        @endforeach
        @foreach(['website_why_choose'=>'Why choose this itinerary','website_travel_tips'=>'Travel tips','website_optional_excursions'=>'Optional excursions'] as $key=>$label)
            <div class="col-md-12 form-group"><label for="{{ $key }}">{{ $label }} (one item per line)</label><textarea id="{{ $key }}" name="{{ $key }}" class="form-control" rows="3">{{ old($key, $package->$key ?? '') }}</textarea></div>
        @endforeach
    </div>
</fieldset>
