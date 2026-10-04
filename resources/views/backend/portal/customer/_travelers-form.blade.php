{{-- Shared traveler form (customer self-service). Expects $traveler (null on create), $relations, $statuses. --}}
@php $t = $traveler ?? null; @endphp
<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1" for="name">{{ ___('label.name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" value="{{ old('name', $t->name ?? '') }}">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="relation">{{ ___('label.relation') }} <span class="text-danger">*</span></label>
        <select id="relation" name="relation" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($relations as $r)
                <option value="{{ $r }}" @selected(old('relation', $t->relation ?? '') === $r)>{{ $r }}</option>
            @endforeach
        </select>
        @error('relation') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="passport_no">{{ ___('label.passport_no') }}</label>
        <input type="text" id="passport_no" name="passport_no" class="form-control input-style-1" value="{{ old('passport_no', $t->passport_no ?? '') }}">
        @error('passport_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="nationality">{{ ___('label.nationality') }}</label>
        <input type="text" id="nationality" name="nationality" class="form-control input-style-1" value="{{ old('nationality', $t->nationality ?? '') }}">
        @error('nationality') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="dob">{{ ___('label.dob') }}</label>
        <input type="date" id="dob" name="dob" class="form-control input-style-1" value="{{ old('dob', $t && $t->dob ? $t->dob->format('Y-m-d') : '') }}">
        @error('dob') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(old('status', $t->status ?? 'Active') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
</div>
<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $t ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cust.travelers') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
