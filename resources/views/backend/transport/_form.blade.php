{{-- Shared Transport Booking form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $customers, $bookings, $types, $statuses. --}}
@php
    $app = $item ?? null;
    $travelDate = old('travel_date', $app && $app->travel_date ? $app->travel_date->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="booking_no">{{ ___('label.booking_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="booking_no" name="booking_no" class="form-control input-style-1" placeholder="{{ ___('label.booking_no') }}" value="{{ old('booking_no', $app->booking_no ?? '') }}">
        @error('booking_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="type">{{ ___('label.type') }} <span class="text-danger">*</span></label>
        <select id="type" name="type" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($types as $tp)
                <option value="{{ $tp }}" @selected(old('type', $app->type ?? '') === $tp)>{{ $tp }}</option>
            @endforeach
        </select>
        @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="direction">{{ ___('label.direction') }} <small class="text-muted">{{ ___('label.airport_only') }}</small></label>
        <select id="direction" name="direction" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($directions as $d)
                <option value="{{ $d }}" @selected(old('direction', $app->direction ?? '') === $d)>{{ $d }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-1">{{ ___('label.pickup_drop_helper') }}</small>
        @error('direction') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="agent_id">{{ ___('label.booked_by_agent') }}</label>
        {{-- The agent portal shows an agent only their own production, and it
             finds it through this column — a trip saved without it is
             invisible to the agent who sold it. --}}
        <select id="agent_id" name="agent_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.direct_office_booking') }}</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" @selected(old('agent_id', $app->agent_id ?? '') == $agent->id)>{{ $agent->name }}@if($agent->email) - {{ $agent->email }}@endif</option>
            @endforeach
        </select>
        @error('agent_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_name">{{ ___('label.customer_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="customer_name" name="customer_name" class="form-control input-style-1" placeholder="{{ ___('label.customer_name') }}" value="{{ old('customer_name', $app->customer_name ?? '') }}">
        @error('customer_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_id">{{ ___('label.customer_account') }} <small class="text-muted">{{ ___('label.optional') }}</small></label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" @selected(old('customer_id', $app->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-1">{{ ___('label.customer_account_hint') }}</small>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    @php
        [$routeFrom, $routeTo] = $app && $app->route && str_contains($app->route, '-')
            ? explode('-', $app->route, 2)
            : [null, null];
    @endphp
    <div class="form-group col-md-4">
        <label class="label-style-1" for="from">{{ ___('label.from') }} <span class="text-danger">*</span></label>
        <input type="text" id="from" name="from" class="form-control input-style-1" placeholder="{{ ___('label.from_code_example') }}" value="{{ old('from', $routeFrom) }}">
        @error('from') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="to">{{ ___('label.to') }} <span class="text-danger">*</span></label>
        <input type="text" id="to" name="to" class="form-control input-style-1" placeholder="{{ ___('label.to_code_example') }}" value="{{ old('to', $routeTo) }}">
        @error('to') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
    @error('route') <div class="form-group col-md-4"><small class="text-danger mt-2 d-block">{{ $message }}</small></div> @enderror

    <div class="form-group col-md-4">
        <label class="label-style-1" for="travel_date">{{ ___('label.travel_date') }} <span class="text-danger">*</span></label>
        <input type="date" id="travel_date" name="travel_date" class="form-control input-style-1" value="{{ $travelDate }}">
        @error('travel_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="vehicle">{{ ___('label.vehicle') }}</label>
        @php $currentVehicle = old('vehicle', $app->vehicle ?? ''); @endphp
        <select id="vehicle" name="vehicle" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @if($currentVehicle && ! $vehicleCategories->contains($currentVehicle))
                {{-- Keeps whatever this booking already had, even if that
                     category has since been renamed or removed. --}}
                <option value="{{ $currentVehicle }}" selected>{{ $currentVehicle }} ({{ ___('label.current') }})</option>
            @endif
            @foreach($vehicleCategories as $vc)
                <option value="{{ $vc }}" @selected($currentVehicle === $vc)>{{ $vc }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-1">Manage this list under Transport &gt; {{ ___('menus.vehicle_categories') }}.</small>
        @error('vehicle') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="driver_id">{{ ___('label.driver') }}</label>
        <select id="driver_id" name="driver_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($drivers as $driver)
                <option value="{{ $driver->id }}" @selected(old('driver_id', $app->driver_id ?? '') == $driver->id)>{{ $driver->name }}</option>
            @endforeach
        </select>
        @error('driver_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="booking_id">{{ ___('label.linked_package_booking') }} <small class="text-muted">{{ ___('label.optional') }}</small></label>
        <select id="booking_id" name="booking_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($bookings as $booking)
                <option value="{{ $booking->id }}" @selected(old('booking_id', $app->booking_id ?? '') == $booking->id)>
                    #{{ $booking->id }} — {{ $booking->package->title ?? ___('label.no_package_fallback') }}@if($booking->travel_date) ({{ $booking->travel_date->format('d M Y') }})@endif
                </option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-1">{{ ___('label.linked_package_hint') }}</small>
        @error('booking_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="fare">{{ ___('label.fare') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="fare" name="fare" class="form-control input-style-1" placeholder="0.00" value="{{ old('fare', $app->fare ?? '') }}">
        @error('fare') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'Booked') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- Read by the receipt when the trip is marked Paid. --}}
    <div class="form-group col-md-4">
        <label class="label-style-1" for="payment_method">{{ ___('label.payment_method') }}</label>
        <select id="payment_method" name="payment_method" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($methods as $m)
                <option value="{{ $m }}" @selected(old('payment_method', $app->payment_method ?? '') === $m)>{{ $m }}</option>
            @endforeach
        </select>
        @error('payment_method') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('transport.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/customer_autofill.js') }}"></script>
@endpush
