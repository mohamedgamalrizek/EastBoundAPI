{{-- Shared Flight Booking form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $customers, $bookings, $statuses, $cities. --}}
@php
    $app = $item ?? null;
    $flightDate = old('flight_date', $app && $app->flight_date ? $app->flight_date->format('Y-m-d') : '');
    // Existing route strings may be "DAC→DXB", "DAC -> DXB" or "Dhaka -> Dubai";
    // split them back and match each side by code or city name, so the
    // From/To pickers prefill on edit.
    $routeParts = $app && $app->route ? preg_split('/\s*(?:→|->|—|–|-)\s*/', trim($app->route), 2) : [];
    $fromValue = old('from', '');
    $toValue   = old('to', '');
    foreach ([0 => 'fromValue', 1 => 'toValue'] as $idx => $target) {
        $part = $routeParts[$idx] ?? '';
        if ($$target === '' && $part !== '') {
            $match = $cities->first(fn ($c) => strcasecmp((string) $c->city, $part) === 0 || (string) $c->code === $part);
            $$target = $match ? $match->city : '';
        }
    }
@endphp

<div class="form-row">
    <input type="hidden" id="customer_id" name="customer_id" value="{{ old('customer_id', $app->customer_id ?? '') }}">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="booking_id">{{ ___('label.main_booking') }} <small class="text-muted">{{ ___('label.optional') }}</small></label>
        <select id="booking_id" name="booking_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.no_main_booking') }}</option>
            @foreach($bookings as $booking)
                <option value="{{ $booking->id }}"
                        data-customer-id="{{ $booking->customer_id }}"
                        data-customer-name="{{ $booking->customer_name }}"
                        @selected(old('booking_id', $app->booking_id ?? '') == $booking->id)>
                    #{{ $booking->id }} - {{ $booking->customer_name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-1">{{ ___('label.main_booking_hint') }}</small>
        @error('booking_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="passenger_name">{{ ___('label.passenger_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="passenger_name" name="passenger_name" class="form-control input-style-1" placeholder="{{ ___('label.name_on_ticket_placeholder') }}" value="{{ old('passenger_name', $app->passenger_name ?? '') }}">
        <small class="text-muted d-block mt-1">{{ ___('label.passenger_name_hint') }}</small>
        @error('passenger_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="pnr">{{ ___('label.pnr') }} <span class="text-danger">*</span></label>
        <input type="text" id="pnr" name="pnr" class="form-control input-style-1" placeholder="PNR" value="{{ old('pnr', $app->pnr ?? '') }}">
        @error('pnr') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="airline">{{ ___('label.airline') }} <span class="text-danger">*</span></label>
        <input type="text" id="airline" name="airline" class="form-control input-style-1" placeholder="{{ ___('label.airline') }}" value="{{ old('airline', $app->airline ?? '') }}">
        @error('airline') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="route_from">{{ ___('label.from') }} <span class="text-danger">*</span></label>
        <select id="route_from" name="from" class="form-control input-style-1 select2" required>
            <option value="">{{ ___('label.select') }}</option>
            @foreach($cities as $c)
                <option value="{{ $c->city }}" data-code="{{ $c->code }}" @selected($fromValue === $c->city)>{{ $c->city }}{{ $c->code ? " ({$c->code})" : '' }}</option>
            @endforeach
        </select>
        @error('route') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="route_to">{{ ___('label.to') }} <span class="text-danger">*</span></label>
        <select id="route_to" name="to" class="form-control input-style-1 select2" required>
            <option value="">{{ ___('label.select') }}</option>
            @foreach($cities as $c)
                <option value="{{ $c->city }}" data-code="{{ $c->code }}" @selected($toValue === $c->city)>{{ $c->city }}{{ $c->code ? " ({$c->code})" : '' }}</option>
            @endforeach
        </select>
        @error('route') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- The composed route (e.g. "DAC -> DXB") is what the DB stores; the
         From/To selects above are just a friendlier way to pick it. --}}
    <input type="hidden" id="route" name="route" value="{{ old('route', $app->route ?? '') }}">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="flight_date">{{ ___('label.flight_date') }} <span class="text-danger">*</span></label>
        <input type="date" id="flight_date" name="flight_date" class="form-control input-style-1" value="{{ $flightDate }}">
        @error('flight_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="ticket_no">{{ ___('label.ticket_no') }}</label>
        <input type="text" id="ticket_no" name="ticket_no" class="form-control input-style-1" placeholder="{{ ___('label.ticket_no') }}" value="{{ old('ticket_no', $app->ticket_no ?? '') }}">
        @error('ticket_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'Confirmed') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ $cancelRoute ?? route('flight.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/pages/flight-form.js') }}?v={{ filemtime(public_path('backend/js/custom/pages/flight-form.js')) }}"></script>
@endpush
