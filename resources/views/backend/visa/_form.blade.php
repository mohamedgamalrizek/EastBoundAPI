{{-- Shared Visa Application form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $application (null on create), $customers, $packages, $visaTypes,
     $documentStatuses, $statuses. --}}
@php
    $app = $application ?? null;
    $dateValue = fn ($field) => old($field, $app && $app->$field ? $app->$field->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="application_no">{{ ___('label.application_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="application_no" name="application_no" class="form-control input-style-1" placeholder="VISA-0000" value="{{ old('application_no', $app->application_no ?? '') }}">
        @error('application_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="applicant_name">{{ ___('label.applicant_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="applicant_name" name="applicant_name" class="form-control input-style-1" placeholder="{{ ___('placeholder.enter_name') }}" value="{{ old('applicant_name', $app->applicant_name ?? '') }}">
        {{-- Not the same person as the customer: one customer often files for a
             whole family, and the embassy only ever sees the traveller's name. --}}
        <small class="text-muted">Whose passport goes to the embassy. Filled from the customer if you leave it blank.</small>
        @error('applicant_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="country">{{ ___('label.country') }} <span class="text-danger">*</span></label>
        <input type="text" id="country" name="country" class="form-control input-style-1" placeholder="{{ ___('label.country') }}" value="{{ old('country', $app->country ?? '') }}">
        @error('country') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="visa_type">{{ ___('label.visa_type') }} <span class="text-danger">*</span></label>
        <select id="visa_type" name="visa_type" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($visaTypes as $type)
                <option value="{{ $type }}" @selected(old('visa_type', $app->visa_type ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('visa_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_id">{{ ___('label.customer') }}</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $app->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        <small class="text-muted">Who books and pays. Invoices and the portal follow this account.</small>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="package_id">{{ ___('label.package') }}</label>
        <select id="package_id" name="package_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($packages as $package)
                <option value="{{ $package->id }}" @selected(old('package_id', $app->package_id ?? '') == $package->id)>{{ $package->title }}</option>
            @endforeach
        </select>
        @error('package_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- Ties the case back to the published catalogue entry, which is what
         makes per-service revenue reporting possible. Picking one fills the
         two fee fields below from the catalogue. --}}
    <div class="form-group col-md-4">
        <label class="label-style-1" for="visa_service_id">{{ ___('label.visa_service') }}</label>
        <select id="visa_service_id" name="visa_service_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($visaServices as $service)
                <option value="{{ $service->id }}"
                        data-govt-fee="{{ (float) $service->govt_fee }}"
                        data-service-fee="{{ (float) $service->service_fee }}"
                        @selected(old('visa_service_id', $app->visa_service_id ?? '') == $service->id)>
                    {{ $service->country }} — {{ $service->visa_type }}
                </option>
            @endforeach
        </select>
        @error('visa_service_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="govt_fee">{{ ___('label.govt_fee') }} ({{ currency_symbol() }})</label>
        <input type="number" step="0.01" min="0" id="govt_fee" name="govt_fee" class="form-control input-style-1"
               data-fill-from="visa_service_id" data-fill-key="govtFee"
               value="{{ old('govt_fee', $app->govt_fee ?? '') }}">
        @error('govt_fee') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="service_fee">{{ ___('label.service_fee') }} ({{ currency_symbol() }})</label>
        <input type="number" step="0.01" min="0" id="service_fee" name="service_fee" class="form-control input-style-1"
               data-fill-from="visa_service_id" data-fill-key="serviceFee"
               value="{{ old('service_fee', $app->service_fee ?? '') }}">
        @error('service_fee') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1">{{ ___('label.total') }}</label>
        <input type="text" class="form-control input-style-1"
               data-total-of="govt_fee,service_fee" data-total-prefix="{{ currency_symbol() }}" readonly>
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="applied_date">{{ ___('label.applied_date') }}</label>
        <input type="date" id="applied_date" name="applied_date" class="form-control input-style-1" value="{{ $dateValue('applied_date') }}">
        @error('applied_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="appointment_date">{{ ___('label.appointment_date') }}</label>
        <input type="date" id="appointment_date" name="appointment_date" class="form-control input-style-1" value="{{ $dateValue('appointment_date') }}">
        @error('appointment_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- A visa only has an expiry once it has been issued, so the field stays
         out of the way until the case is Approved. Filling it earlier put
         cases that were never issued into Expiry Management. --}}
    <div class="form-group col-md-4" id="expiry_date_group">
        <label class="label-style-1" for="expiry_date">{{ ___('label.expiry_date') }}</label>
        <input type="date" id="expiry_date" name="expiry_date" class="form-control input-style-1" value="{{ $dateValue('expiry_date') }}">
        <small class="text-muted">Set once the visa is issued — available when the status is Approved.</small>
        @error('expiry_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="documents_status">{{ ___('label.documents_status') }} <span class="text-danger">*</span></label>
        <select id="documents_status" name="documents_status" class="form-control input-style-1 select2">
            @foreach($documentStatuses as $ds)
                <option value="{{ $ds }}" @selected(old('documents_status', $app->documents_status ?? 'Pending') === $ds)>{{ $ds }}</option>
            @endforeach
        </select>
        @error('documents_status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'Processing') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('visa.applications') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/pages/visa-form.js') }}?v={{ filemtime(public_path('backend/js/custom/pages/visa-form.js')) }}"></script>
@endpush
