{{-- Shared Account form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $types. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="code">{{ ___('label.code') }} <span class="text-danger">*</span></label>
        <input type="text" id="code" name="code" class="form-control input-style-1" placeholder="{{ ___('label.code') }}" value="{{ old('code', $item->code ?? '') }}">
        @error('code') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="name">{{ ___('label.name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" placeholder="{{ ___('label.name') }}" value="{{ old('name', $item->name ?? '') }}">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="type">{{ ___('label.type') }} <span class="text-danger">*</span></label>
        <select id="type" name="type" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($types as $t)
                <option value="{{ $t }}" @selected(old('type', $item->type ?? '') === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="opening_balance">Opening Balance <span class="text-danger">*</span></label>
        <input type="number" step="0.01" id="opening_balance" name="opening_balance" class="form-control input-style-1" placeholder="0.00" value="{{ old('opening_balance', $item->opening_balance ?? '0') }}">
        {{-- The live balance is derived from the journal, so only the starting
             figure is entered here. --}}
        <small class="text-muted">
            What this account started at. The current balance is calculated from the transactions posted to it
            @if($item) — now <b>{{ currency_symbol() }}{{ number_format($item->balance, 2) }}</b>@endif.
        </small>
        @error('opening_balance') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="cash_type">Cash / Bank</label>
        <select id="cash_type" name="cash_type" class="form-control input-style-1 select2">
            @foreach($cashTypes as $ct)
                <option value="{{ $ct }}" @selected(old('cash_type', $item->cash_type ?? 'none') === $ct)>
                    {{ $ct === 'none' ? 'Neither' : ucfirst($ct) }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Accounts marked Cash or Bank are the ones the Cash Book and Bank Book report on.</small>
        @error('cash_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('acc.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
