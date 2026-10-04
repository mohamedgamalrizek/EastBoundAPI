{{-- Shared Agent Invoice form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $agents, $statuses. --}}
@php
    $app = $item ?? null;
    $issuedOn = old('issued_on', $app && $app->issued_on ? $app->issued_on->format('Y-m-d') : '');
    $dueOn    = old('due_on', $app && $app->due_on ? $app->due_on->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="agent_id">{{ ___('label.agent') }}</label>
        <select id="agent_id" name="agent_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" @selected(old('agent_id', $app->agent_id ?? '') == $agent->id)>{{ $agent->name }}</option>
            @endforeach
        </select>
        @error('agent_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="invoice_no">{{ ___('label.invoice_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="invoice_no" name="invoice_no" class="form-control input-style-1" placeholder="{{ ___('label.invoice_no') }}" value="{{ old('invoice_no', $app->invoice_no ?? '') }}">
        @error('invoice_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_name">{{ ___('label.customer_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="customer_name" name="customer_name" class="form-control input-style-1" placeholder="{{ ___('label.customer_name') }}" value="{{ old('customer_name', $app->customer_name ?? '') }}">
        @error('customer_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="amount">{{ ___('label.amount') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="amount" name="amount" class="form-control input-style-1" placeholder="0.00" value="{{ old('amount', $app->amount ?? '') }}">
        @error('amount') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="issued_on">{{ ___('label.issued_on') }}</label>
        <input type="date" id="issued_on" name="issued_on" class="form-control input-style-1" value="{{ $issuedOn }}">
        @error('issued_on') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="due_on">{{ ___('label.due_on') }}</label>
        <input type="date" id="due_on" name="due_on" class="form-control input-style-1" value="{{ $dueOn }}">
        @error('due_on') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'unpaid') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- Marking an invoice paid moves real money: Wallet nets it off the
         agent's commission balance, the others record cash/bank arriving. --}}
    <div class="form-group col-md-4">
        <label class="label-style-1" for="method">{{ ___('label.payment_method') }} <small class="text-muted">(when paid)</small></label>
        <select id="method" name="method" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($methods as $m)
                <option value="{{ $m }}" @selected(old('method', $app->method ?? '') === $m)>{{ $m }}</option>
            @endforeach
        </select>
        @error('method') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="paid_on">{{ ___('label.settled_on') }}</label>
        <input type="date" id="paid_on" name="paid_on" class="form-control input-style-1" value="{{ old('paid_on', $app && $app->paid_on ? $app->paid_on->format('Y-m-d') : '') }}">
        @error('paid_on') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('agent.invoice.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
