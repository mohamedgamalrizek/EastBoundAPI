{{-- Shared subscription form. Expects $subscription (null on create), $tenants, $plans, $statuses. --}}
@php $sub = $subscription ?? null; @endphp
<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1" for="tenant_id">Tenant <span class="text-danger">*</span></label>
        <select id="tenant_id" name="tenant_id" class="form-control input-style-1 select2">
            <option value="">— Select —</option>
            @foreach($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $sub->tenant_id ?? '') == $tenant->id)>{{ $tenant->name }}</option>
            @endforeach
        </select>
        @error('tenant_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="plan_id">Plan <span class="text-danger">*</span></label>
        <select id="plan_id" name="plan_id" class="form-control input-style-1 select2">
            <option value="">— Select —</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->id }}" @selected(old('plan_id', $sub->plan_id ?? '') == $plan->id)>{{ $plan->name }} (৳{{ number_format($plan->price) }})</option>
            @endforeach
        </select>
        @error('plan_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="label-style-1" for="status">Status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $sub->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="label-style-1" for="starts_at">Starts</label>
        <input type="date" id="starts_at" name="starts_at" class="form-control input-style-1" value="{{ old('starts_at', $sub && $sub->starts_at ? $sub->starts_at->format('Y-m-d') : '') }}">
        @error('starts_at') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="label-style-1" for="ends_at">Renews / Ends</label>
        <input type="date" id="ends_at" name="ends_at" class="form-control input-style-1" value="{{ old('ends_at', $sub && $sub->ends_at ? $sub->ends_at->format('Y-m-d') : '') }}">
        @error('ends_at') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="label-style-1" for="amount">Amount (৳) <small class="text-muted">(blank = plan price)</small></label>
        <input type="number" step="0.01" min="0" id="amount" name="amount" class="form-control input-style-1" value="{{ old('amount', $sub->amount ?? '') }}">
        @error('amount') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
</div>
<div class="j-create-btns"><div class="drp-btns">
    <button type="submit" class="j-td-btn">{{ $sub ? 'Save Changes' : 'Save' }}</button>
    <a href="{{ route('saas.subscriptions') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
</div></div>
