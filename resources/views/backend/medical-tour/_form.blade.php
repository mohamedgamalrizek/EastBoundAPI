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
        <label class="label-style-1" for="patient_name">Patient Name <span class=\"text-danger\">*</span></label>
        <input type="text" id="patient_name" name="patient_name" class="form-control input-style-1" placeholder="Patient Name" value="{{ old('patient_name', $app->patient_name ?? '') }}">
        @error('patient_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="destination">Destination <span class=\"text-danger\">*</span></label>
        <input type="text" id="destination" name="destination" class="form-control input-style-1" placeholder="e.g. Bangkok, Thailand" value="{{ old('destination', $app->destination ?? '') }}">
        @error('destination') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="hospital">Hospital</label>
        <input type="text" id="hospital" name="hospital" class="form-control input-style-1" placeholder="Hospital" value="{{ old('hospital', $app->hospital ?? '') }}">
        @error('hospital') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="treatment">Treatment</label>
        <input type="text" id="treatment" name="treatment" class="form-control input-style-1" placeholder="Treatment" value="{{ old('treatment', $app->treatment ?? '') }}">
        @error('treatment') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="cost">Cost</label>
        <input type="number" step=\"0.01\" id="cost" name="cost" class="form-control input-style-1" placeholder="Cost" value="{{ old('cost', $app->cost ?? '') }}">
        @error('cost') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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
        <a href="{{ route('medical-tour.index') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
    </div>
</div>
