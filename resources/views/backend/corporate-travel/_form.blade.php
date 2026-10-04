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
        <label class="label-style-1" for="company_name">Company <span class=\"text-danger\">*</span></label>
        <input type="text" id="company_name" name="company_name" class="form-control input-style-1" placeholder="Company" value="{{ old('company_name', $app->company_name ?? '') }}">
        @error('company_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="contact_person">Contact Person</label>
        <input type="text" id="contact_person" name="contact_person" class="form-control input-style-1" placeholder="Contact Person" value="{{ old('contact_person', $app->contact_person ?? '') }}">
        @error('contact_person') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="service_type">Service Type <span class=\"text-danger\">*</span></label>
        <select id="service_type" name="service_type" class="form-control input-style-1 select2" placeholder="Service Type">
            <option value=""></option>
            @foreach($service_type_options as $opt)
                <option value="{{ $opt }}" @selected(old('service_type', $app->service_type ?? '') == $opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('service_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="employees">Employees</label>
        <input type="number" id="employees" name="employees" class="form-control input-style-1" placeholder="Employees" value="{{ old('employees', $app->employees ?? '') }}">
        @error('employees') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="budget">Budget</label>
        <input type="number" step=\"0.01\" id="budget" name="budget" class="form-control input-style-1" placeholder="Budget" value="{{ old('budget', $app->budget ?? '') }}">
        @error('budget') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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
        <a href="{{ route('corporate-travel.index') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
    </div>
</div>
