{{-- Shared Agent Commission form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $agents, $customers, $statuses. --}}
@php
    $app = $item ?? null;
    $earnedOn = old('earned_on', $app && $app->earned_on ? $app->earned_on->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="reference">{{ ___('label.reference') }} <span class="text-danger">*</span></label>
        <input type="text" id="reference" name="reference" class="form-control input-style-1" placeholder="{{ ___('label.reference') }}" value="{{ old('reference', $app->reference ?? '') }}">
        @error('reference') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

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
        <label class="label-style-1" for="customer_id">{{ ___('label.customer') }}</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $app->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="booking_ref">{{ ___('label.booking_ref') }} <span class="text-danger">*</span></label>
        <input type="text" id="booking_ref" name="booking_ref" class="form-control input-style-1" placeholder="{{ ___('label.booking_ref') }}" value="{{ old('booking_ref', $app->booking_ref ?? '') }}">
        @error('booking_ref') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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
        <label class="label-style-1" for="rate">{{ ___('label.rate') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="rate" name="rate" class="form-control input-style-1" placeholder="0.00" value="{{ old('rate', $app->rate ?? '') }}">
        @error('rate') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'pending') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="earned_on">{{ ___('label.earned_on') }}</label>
        <input type="date" id="earned_on" name="earned_on" class="form-control input-style-1" value="{{ $earnedOn }}">
        @error('earned_on') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('agent.commission.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
