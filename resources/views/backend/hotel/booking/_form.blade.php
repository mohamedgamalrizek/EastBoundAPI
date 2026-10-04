{{-- Shared Hotel Booking form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $hotels, $customers, $rooms, $statuses. --}}
@php
    $app = $item ?? null;
    $checkIn  = old('check_in', $app && $app->check_in ? $app->check_in->format('Y-m-d') : '');
    $checkOut = old('check_out', $app && $app->check_out ? $app->check_out->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="booking_no">{{ ___('label.booking_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="booking_no" name="booking_no" class="form-control input-style-1" placeholder="{{ ___('label.booking_no') }}" value="{{ old('booking_no', $app->booking_no ?? '') }}">
        @error('booking_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="hotel_id">{{ ___('label.hotel') }} <span class="text-danger">*</span></label>
        <select id="hotel_id" name="hotel_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($hotels as $hotel)
                <option value="{{ $hotel->id }}" @selected(old('hotel_id', $app->hotel_id ?? '') == $hotel->id)>{{ $hotel->name }}</option>
            @endforeach
        </select>
        @error('hotel_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_id">{{ ___('label.customer') }}</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $app->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="agent_id">Booked by (Agent)</label>
        {{-- The agent portal shows an agent only their own production, and it
             finds it through this column — a stay saved without it is
             invisible to the agent who sold it. --}}
        <select id="agent_id" name="agent_id" class="form-control input-style-1 select2">
            <option value="">-- Direct / office booking --</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" @selected(old('agent_id', $app->agent_id ?? '') == $agent->id)>{{ $agent->name }}@if($agent->email) - {{ $agent->email }}@endif</option>
            @endforeach
        </select>
        @error('agent_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="hotel_room_id">{{ ___('label.room') }} <span class="text-danger">*</span></label>
        <select id="hotel_room_id" name="hotel_room_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}" data-hotel-id="{{ $room->hotel_id }}" @selected(old('hotel_room_id', $app->hotel_room_id ?? '') == $room->id)>{{ $room->room_type }}</option>
            @endforeach
        </select>
        @error('hotel_room_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="guest_name">{{ ___('label.guest_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="guest_name" name="guest_name" class="form-control input-style-1" placeholder="{{ ___('label.guest_name') }}" value="{{ old('guest_name', $app->guest_name ?? '') }}">
        @error('guest_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="check_in">{{ ___('label.check_in') }} <span class="text-danger">*</span></label>
        <input type="date" id="check_in" name="check_in" class="form-control input-style-1" value="{{ $checkIn }}">
        @error('check_in') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="check_out">{{ ___('label.check_out') }} <span class="text-danger">*</span></label>
        <input type="date" id="check_out" name="check_out" class="form-control input-style-1" value="{{ $checkOut }}">
        @error('check_out') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="nights">{{ ___('label.nights') }} <span class="text-danger">*</span></label>
        <input type="number" id="nights" name="nights" min="0" class="form-control input-style-1" placeholder="0" value="{{ old('nights', $app->nights ?? '') }}">
        @error('nights') <small class="text-danger mt-2">{{ $message }}</small> @enderror
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
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'Booked') === $st)>{{ $st }}</option>
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
        <a href="{{ $cancelRoute ?? route('hotel.booking.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/hotel_booking_room_filter.js') }}"></script>
@endpush
