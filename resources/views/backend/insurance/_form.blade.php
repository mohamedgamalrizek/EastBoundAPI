@php $app = $item ?? null; @endphp
<div class="form-row">

    {{-- Links the sale to a customer record so it shows on their profile and
         the fee can be reported; both stay optional for walk-in enquiries. --}}
    <div class="form-group col-md-6">
        <label class="label-style-1" for="customer_id">{{ ___('label.customer') }}</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2" placeholder="{{ ___('label.customer') }}">
            <option value=""></option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $app->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="service_fee">{{ ___('label.service_fee') }} ({{ currency_symbol() }})</label>
        <input type="number" step="0.01" min="0" id="service_fee" name="service_fee" class="form-control input-style-1"
               placeholder="{{ ___('label.service_fee') }}" value="{{ old('service_fee', $app->service_fee ?? '') }}">
        @error('service_fee') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label class="label-style-1" for="provider">Provider <span class=\"text-danger\">*</span></label>
        <input type="text" id="provider" name="provider" class="form-control input-style-1" placeholder="Provider" value="{{ old('provider', $app->provider ?? '') }}">
        @error('provider') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="plan_name">Plan Name <span class=\"text-danger\">*</span></label>
        <input type="text" id="plan_name" name="plan_name" class="form-control input-style-1" placeholder="Plan Name" value="{{ old('plan_name', $app->plan_name ?? '') }}">
        @error('plan_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="type">Type <span class=\"text-danger\">*</span></label>
        <select id="type" name="type" class="form-control input-style-1 select2" placeholder="Type">
            <option value=""></option>
            @foreach($type_options as $opt)
                <option value="{{ $opt }}" @selected(old('type', $app->type ?? '') == $opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="coverage">Coverage</label>
        <input type="number" step=\"0.01\" id="coverage" name="coverage" class="form-control input-style-1" placeholder="Coverage" value="{{ old('coverage', $app->coverage ?? '') }}">
        @error('coverage') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="premium">Premium</label>
        <input type="number" step=\"0.01\" id="premium" name="premium" class="form-control input-style-1" placeholder="Premium" value="{{ old('premium', $app->premium ?? '') }}">
        @error('premium') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">Status <span class=\"text-danger\">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2" placeholder="Status">
            <option value=""></option>
            @foreach($status_options as $opt)
                <option value="{{ $opt }}" @selected(old('status', $app->status ?? '') == $opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? 'Save Changes' : 'Save' }}</button>
        <a href="{{ route('insurance.index') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
    </div>
</div>
