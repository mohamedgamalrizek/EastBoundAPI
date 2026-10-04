{{-- Shared Tour Schedule form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $packages, $statuses. --}}
@php
    $app = $item ?? null;
    $startDate = old('start_date', $app && $app->start_date ? $app->start_date->format('Y-m-d') : '');
    $endDate   = old('end_date', $app && $app->end_date ? $app->end_date->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="package_id">{{ ___('label.package') }}</label>
        <select id="package_id" name="package_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($packages as $package)
                <option value="{{ $package->id }}" @selected(old('package_id', $app->package_id ?? '') == $package->id)>{{ $package->title }}</option>
            @endforeach
        </select>
        @error('package_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="package_title">{{ ___('label.package_title') }} <span class="text-danger">*</span></label>
        <input type="text" id="package_title" name="package_title" class="form-control input-style-1" placeholder="{{ ___('label.package_title') }}" value="{{ old('package_title', $app->package_title ?? '') }}">
        @error('package_title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="start_date">{{ ___('label.start_date') }}</label>
        <input type="date" id="start_date" name="start_date" class="form-control input-style-1" value="{{ $startDate }}">
        @error('start_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="end_date">{{ ___('label.end_date') }}</label>
        <input type="date" id="end_date" name="end_date" class="form-control input-style-1" value="{{ $endDate }}">
        @error('end_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="seats">{{ ___('label.seats') }} <span class="text-danger">*</span></label>
        <input type="number" id="seats" name="seats" min="0" class="form-control input-style-1" placeholder="0" value="{{ old('seats', $app->seats ?? 0) }}">
        @error('seats') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="booked">{{ ___('label.booked') }} <span class="text-danger">*</span></label>
        <input type="number" id="booked" name="booked" min="0" class="form-control input-style-1" placeholder="0" value="{{ old('booked', $app->booked ?? 0) }}">
        @error('booked') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'open') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('tour.schedule.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
