{{-- Shared contract fields. Wrapped by contract-create / contract-edit. --}}
@php $item = $item ?? null; @endphp
<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="supplier_id">Supplier <span class="text-danger">*</span></label>
        <select id="supplier_id" name="supplier_id" class="form-control input-style-1 select2">
            <option value="">Select a supplier</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected((int) old('supplier_id', $item->supplier_id ?? 0) === $s->id)>
                    {{ $s->name }} ({{ $s->type }})
                </option>
            @endforeach
        </select>
        @error('supplier_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="contract_no">Contract No <span class="text-danger">*</span></label>
        <input type="text" id="contract_no" name="contract_no" class="form-control input-style-1"
               placeholder="SUP-2026-001" value="{{ old('contract_no', $item->contract_no ?? '') }}">
        @error('contract_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="title">Title <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1"
               placeholder="Annual net-rate agreement" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="rate_type">Rate type <span class="text-danger">*</span></label>
        <select id="rate_type" name="rate_type" class="form-control input-style-1 select2">
            @foreach($rateTypes as $type)
                <option value="{{ $type }}" @selected(old('rate_type', $item->rate_type ?? 'Fixed') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('rate_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="value">Contract value</label>
        <input type="number" step="0.01" min="0" id="value" name="value" class="form-control input-style-1"
               placeholder="0.00" value="{{ old('value', $item->value ?? '') }}">
        @error('value') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="commission_rate">Commission %</label>
        <input type="number" step="0.01" min="0" max="100" id="commission_rate" name="commission_rate"
               class="form-control input-style-1" placeholder="Only for a commission deal"
               value="{{ old('commission_rate', $item->commission_rate ?? '') }}">
        @error('commission_rate') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="credit_days">Credit days</label>
        <input type="number" min="0" max="365" id="credit_days" name="credit_days" class="form-control input-style-1"
               placeholder="30" value="{{ old('credit_days', $item->credit_days ?? '') }}">
        @error('credit_days') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="start_date">Start date <span class="text-danger">*</span></label>
        <input type="date" id="start_date" name="start_date" class="form-control input-style-1"
               value="{{ old('start_date', optional($item?->start_date)->format('Y-m-d')) }}">
        @error('start_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="end_date">End date</label>
        <input type="date" id="end_date" name="end_date" class="form-control input-style-1"
               value="{{ old('end_date', optional($item?->end_date)->format('Y-m-d')) }}">
        @error('end_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-3">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $item->status ?? 'Active') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="terms">Terms</label>
        <textarea id="terms" name="terms" rows="3" class="form-control input-style-1"
                  placeholder="Cancellation window, blackout dates, payment terms…">{{ old('terms', $item->terms ?? '') }}</textarea>
        @error('terms') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('sup.contracts') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
