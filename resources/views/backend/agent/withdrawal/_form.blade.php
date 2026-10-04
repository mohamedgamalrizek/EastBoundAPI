{{-- Shared Agent Payout form. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $agents, $available, $methods, $statuses, $nextRef. --}}
@php
    $requestedOn = old('requested_on', $item && $item->requested_on ? $item->requested_on->format('Y-m-d') : date('Y-m-d'));
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="agent_id">{{ ___('label.agent') }} <span class="text-danger">*</span></label>
        <select id="agent_id" name="agent_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}"
                        data-available="{{ $available[$agent->id] ?? 0 }}"
                        @selected(old('agent_id', $item->agent_id ?? '') == $agent->id)>
                    {{ $agent->name }} — available {{ currency_symbol() }}{{ number_format($available[$agent->id] ?? 0, 2) }}
                </option>
            @endforeach
        </select>
        {{-- "Available" is the wallet balance less payouts already requested,
             so the same commission cannot go out twice. --}}
        <small class="text-muted">Only approved commissions are withdrawable.</small>
        @error('agent_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="reference">{{ ___('label.reference') }}</label>
        <input type="text" id="reference" name="reference" class="form-control input-style-1"
               placeholder="{{ $nextRef }}" value="{{ old('reference', $item->reference ?? '') }}">
        @error('reference') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="amount">{{ ___('label.amount') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" id="amount" name="amount" class="form-control input-style-1" placeholder="0.00" value="{{ old('amount', $item->amount ?? '') }}">
        @error('amount') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="method">{{ ___('label.method') }} <span class="text-danger">*</span></label>
        <select id="method" name="method" class="form-control input-style-1 select2">
            @foreach($methods as $m)
                <option value="{{ $m }}" @selected(old('method', $item->method ?? 'Bank') === $m)>{{ $m }}</option>
            @endforeach
        </select>
        @error('method') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="account_details">Account / number</label>
        <input type="text" id="account_details" name="account_details" class="form-control input-style-1" placeholder="City Bank ****4421" value="{{ old('account_details', $item->account_details ?? '') }}">
        @error('account_details') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="requested_on">Requested on</label>
        <input type="date" id="requested_on" name="requested_on" class="form-control input-style-1" value="{{ $requestedOn }}">
        @error('requested_on') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }}</label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'requested') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        {{-- Setting this to Paid here does the same as the Mark paid button:
             the wallet is debited and the payment is posted. --}}
        <small class="text-muted">Paid debits the wallet and posts the cash payment.</small>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-8">
        <label class="label-style-1" for="note">Note</label>
        <input type="text" id="note" name="note" class="form-control input-style-1" placeholder="Optional" value="{{ old('note', $item->note ?? '') }}">
        @error('note') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('agent.withdrawal.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
