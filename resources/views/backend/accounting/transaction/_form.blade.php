{{-- Shared Account Transaction form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $accounts, $types. --}}
@php
    $txnDate = old('txn_date', $item && $item->txn_date ? $item->txn_date->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="account_id">{{ ___('label.account') }} <span class="text-danger">*</span></label>
        <select id="account_id" name="account_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('account_id', $item->account_id ?? '') == $account->id)>
                    {{ $account->code }} — {{ $account->name }} ({{ $account->type }})
                </option>
            @endforeach
        </select>
        <small class="text-muted">What the money is for: the income or expense account.</small>
        @error('account_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- The second leg. An entry with only one account cannot be posted as a
         debit and a credit, which is what kept the old trial balance from
         ever agreeing. --}}
    <div class="form-group col-md-4">
        <label class="label-style-1" for="contra_account_id">Paid from / received into <span class="text-danger">*</span></label>
        <select id="contra_account_id" name="contra_account_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('contra_account_id', $item->contra_account_id ?? '') == $account->id)>
                    {{ $account->code }} — {{ $account->name }} ({{ $account->type }})
                </option>
            @endforeach
        </select>
        <small class="text-muted">Usually Cash or Bank. Income credits the account above and debits this one; expenses do the reverse.</small>
        @error('contra_account_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="txn_date">{{ ___('label.date') }} <span class="text-danger">*</span></label>
        <input type="date" id="txn_date" name="txn_date" class="form-control input-style-1" value="{{ $txnDate }}">
        @error('txn_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="account_name">{{ ___('label.account_name') }}</label>
        <input type="text" id="account_name" name="account_name" class="form-control input-style-1" placeholder="Leave blank to use the account's name" value="{{ old('account_name', $item->account_name ?? '') }}">
        @error('account_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="type">{{ ___('label.type') }} <span class="text-danger">*</span></label>
        <select id="type" name="type" class="form-control input-style-1 select2">
            @foreach($types as $t)
                <option value="{{ $t }}" @selected(old('type', $item->type ?? 'income') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="amount">{{ ___('label.amount') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" id="amount" name="amount" class="form-control input-style-1" placeholder="0.00" value="{{ old('amount', $item->amount ?? '') }}">
        @error('amount') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="reference">{{ ___('label.reference') }}</label>
        <input type="text" id="reference" name="reference" class="form-control input-style-1" placeholder="{{ ___('label.reference') }}" value="{{ old('reference', $item->reference ?? '') }}">
        @error('reference') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="description">{{ ___('label.description') }}</label>
        <textarea id="description" name="description" rows="3" class="form-control input-style-1" placeholder="{{ ___('label.description') }}">{{ old('description', $item->description ?? '') }}</textarea>
        @error('description') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('acc.txn.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
