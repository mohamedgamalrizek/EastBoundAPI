{{-- Shared Event Booking form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $eventTours, $customers, $statuses, $methods. --}}
@php
    $app = $item ?? null;
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="booking_no">{{ ___('label.booking_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="booking_no" name="booking_no" class="form-control input-style-1" placeholder="{{ ___('label.booking_no') }}" value="{{ old('booking_no', $app->booking_no ?? '') }}">
        @error('booking_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="event_tour_id">{{ ___('label.event') }} <span class="text-danger">*</span></label>
        <select id="event_tour_id" name="event_tour_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($eventTours as $event)
                <option value="{{ $event->id }}" @selected(old('event_tour_id', $app->event_tour_id ?? '') == $event->id)>{{ $event->title }}</option>
            @endforeach
        </select>
        @error('event_tour_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_name">{{ ___('label.customer_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="customer_name" name="customer_name" class="form-control input-style-1" placeholder="{{ ___('label.customer_name') }}" value="{{ old('customer_name', $app->customer_name ?? '') }}">
        @error('customer_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_id">Customer Account <small class="text-muted">(optional)</small></label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" @selected(old('customer_id', $app->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-1">Link this booking to a registered customer account, if one exists.</small>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="seats">{{ ___('label.seats') }} <span class="text-danger">*</span></label>
        <input type="number" id="seats" name="seats" min="1" class="form-control input-style-1" placeholder="1" value="{{ old('seats', $app->seats ?? 1) }}">
        @error('seats') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="amount">{{ ___('label.amount') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="amount" name="amount" class="form-control input-style-1" placeholder="0.00" value="{{ old('amount', $app->amount ?? '') }}">
        @error('amount') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'Pending') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- Read by the receipt when the booking is marked Paid. --}}
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
        <a href="{{ route('event-tour.booking.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/customer_autofill.js') }}"></script>
@endpush
