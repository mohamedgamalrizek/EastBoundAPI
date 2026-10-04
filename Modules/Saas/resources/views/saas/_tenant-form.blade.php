{{-- Shared tenant form. Expects $tenant (null on create), $plans, $statuses. --}}
@php $t = $tenant ?? null; @endphp
<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1" for="name">Company Name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" value="{{ old('name', $t->name ?? '') }}" placeholder="e.g. Skyline Travels">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="email">Contact Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control input-style-1" value="{{ old('email', $t->email ?? '') }}" placeholder="owner@company.com">
        @error('email') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="plan_id">Plan</label>
        <select id="plan_id" name="plan_id" class="form-control input-style-1 select2">
            <option value="">— None —</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->id }}" @selected(old('plan_id', $t->plan_id ?? '') == $plan->id)>{{ $plan->name }} (৳{{ number_format($plan->price) }}/{{ $plan->billing_cycle }})</option>
            @endforeach
        </select>
        @error('plan_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="status">Status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $t->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    @unless($t)
    <div class="form-group col-md-8">
        <label class="label-style-1" for="domain">Domain <small class="text-muted">(optional, e.g. skyline.flow.test — manage more under Domains)</small></label>
        <input type="text" id="domain" name="domain" class="form-control input-style-1" value="{{ old('domain') }}" placeholder="company.flow.test">
        @error('domain') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-4 d-flex align-items-end">
        <div class="custom-control custom-checkbox mb-2">
            <input type="checkbox" class="custom-control-input" id="provision" name="provision" value="1" @checked(old('provision'))>
            <label class="custom-control-label" for="provision">Provision database now</label>
        </div>
    </div>
    @endunless
</div>
<div class="j-create-btns"><div class="drp-btns">
    <button type="submit" class="j-td-btn">{{ $t ? 'Save Changes' : 'Create Tenant' }}</button>
    <a href="{{ route('saas.tenants') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
</div></div>
