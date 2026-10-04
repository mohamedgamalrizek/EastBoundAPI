{{-- Shared plan form. Expects $plan (null on create), $cycles. --}}
@php $p = $plan ?? null; @endphp
<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1" for="name">Plan Name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" value="{{ old('name', $p->name ?? '') }}" placeholder="e.g. Growth">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="label-style-1" for="price">Price (৳) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="price" name="price" class="form-control input-style-1" value="{{ old('price', $p->price ?? '') }}">
        @error('price') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="label-style-1" for="billing_cycle">Billing Cycle <span class="text-danger">*</span></label>
        <select id="billing_cycle" name="billing_cycle" class="form-control input-style-1 select2">
            @foreach($cycles as $c)
                <option value="{{ $c }}" @selected(old('billing_cycle', $p->billing_cycle ?? 'monthly') === $c)>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        @error('billing_cycle') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="label-style-1" for="max_users">Max Users <small class="text-muted">(blank = unlimited)</small></label>
        <input type="number" min="1" id="max_users" name="max_users" class="form-control input-style-1" value="{{ old('max_users', $p->max_users ?? '') }}">
        @error('max_users') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="label-style-1" for="status">Status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            <option value="1" @selected(old('status', $p->status ?? 1) == 1)>Active</option>
            <option value="0" @selected(old('status', $p->status ?? 1) == 0)>Inactive</option>
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-12">
        <label class="label-style-1" for="features">Features <small class="text-muted">(one per line)</small></label>
        <textarea id="features" name="features" rows="5" class="form-control input-style-1" placeholder="Up to 20 users&#10;Bookings & Customers&#10;Priority support">{{ old('features', $p ? collect($p->features)->implode("\n") : '') }}</textarea>
        @error('features') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
</div>
<div class="j-create-btns"><div class="drp-btns">
    <button type="submit" class="j-td-btn">{{ $p ? 'Save Changes' : 'Save' }}</button>
    <a href="{{ route('saas.plans') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
</div></div>
