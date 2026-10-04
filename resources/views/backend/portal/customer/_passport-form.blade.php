{{-- Shared passport form (customer self-service). Expects $passport (null on create), $statuses. --}}
@php $p = $passport ?? null; @endphp
<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1" for="holder_name">{{ ___('label.holder_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="holder_name" name="holder_name" class="form-control input-style-1" value="{{ old('holder_name', $p->holder_name ?? '') }}">
        @error('holder_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="passport_no">{{ ___('label.passport_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="passport_no" name="passport_no" class="form-control input-style-1" value="{{ old('passport_no', $p->passport_no ?? '') }}">
        @error('passport_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="nationality">{{ ___('label.nationality') }}</label>
        <input type="text" id="nationality" name="nationality" class="form-control input-style-1" value="{{ old('nationality', $p->nationality ?? '') }}">
        @error('nationality') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="issue_date">{{ ___('label.issue_date') }}</label>
        <input type="date" id="issue_date" name="issue_date" class="form-control input-style-1" value="{{ old('issue_date', $p && $p->issue_date ? $p->issue_date->format('Y-m-d') : '') }}">
        @error('issue_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="expiry_date">{{ ___('label.expiry_date') }}</label>
        <input type="date" id="expiry_date" name="expiry_date" class="form-control input-style-1" value="{{ old('expiry_date', $p && $p->expiry_date ? $p->expiry_date->format('Y-m-d') : '') }}">
        @error('expiry_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
</div>
<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $p ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cust.passport') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
