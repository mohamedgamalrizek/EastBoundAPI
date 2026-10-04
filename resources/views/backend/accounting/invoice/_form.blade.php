{{-- Shared Invoice form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $customers, $bookings, $statuses. --}}
@php
    $issueDate = old('issue_date', $item && $item->issue_date ? $item->issue_date->format('Y-m-d') : '');
    $dueDate   = old('due_date', $item && $item->due_date ? $item->due_date->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="invoice_no">{{ ___('label.invoice_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="invoice_no" name="invoice_no" class="form-control input-style-1" placeholder="{{ ___('label.invoice_no') }}" value="{{ old('invoice_no', $item->invoice_no ?? '') }}">
        @error('invoice_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="booking_id">Related Booking</label>
        <select id="booking_id" name="booking_id" class="form-control input-style-1 select2">
            <option value="">No booking / manual invoice</option>
            @foreach($bookings as $booking)
                <option
                    value="{{ $booking->id }}"
                    data-customer-id="{{ $booking->customer_id }}"
                    data-customer-name="{{ $booking->customer_name }}"
                    data-amount="{{ $booking->amount }}"
                    @selected(old('booking_id', $item->booking_id ?? '') == $booking->id)>
                    #{{ $booking->id }} - {{ $booking->customer_name }}@if($booking->travel_date) ({{ $booking->travel_date->format('Y-m-d') }})@endif
                </option>
            @endforeach
        </select>
        <small class="text-muted">Optional. Select a booking to fill customer and amount automatically.</small>
        @error('booking_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_id">Invoice To</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">Walk-in / not saved as customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" @selected(old('customer_id', $item->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        <small class="text-muted">Links this invoice to a saved customer account.</small>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_name">Billing Name <span class="text-danger">*</span></label>
        <input type="text" id="customer_name" name="customer_name" class="form-control input-style-1" placeholder="Name printed on invoice" value="{{ old('customer_name', $item->customer_name ?? '') }}">
        <small class="text-muted">Printed on the invoice. This can differ from the saved customer record.</small>
        @error('customer_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="issue_date">{{ ___('label.issue_date') }} <span class="text-danger">*</span></label>
        <input type="date" id="issue_date" name="issue_date" class="form-control input-style-1" value="{{ $issueDate }}">
        @error('issue_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="due_date">{{ ___('label.due_date') }}</label>
        <input type="date" id="due_date" name="due_date" class="form-control input-style-1" value="{{ $dueDate }}">
        @error('due_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="amount">{{ ___('label.amount') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="amount" name="amount" class="form-control input-style-1" placeholder="0.00" value="{{ old('amount', $item->amount ?? '') }}">
        @error('amount') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    {{-- Status and paid amount come from the receipts recorded against this
         invoice, so they can never disagree with the money received. --}}
    <div class="form-group col-md-4">
        <label class="label-style-1">{{ ___('label.status') }}</label>
        <div class="form-control input-style-1 d-flex align-items-center justify-content-between tv-surface-soft">
            <span class="bullet-badge bullet-badge-{{ ($item->status ?? 'unpaid') === 'paid' ? 'success' : (($item->status ?? '') === 'overdue' ? 'danger' : 'warning') }}">
                {{ ucfirst($item->status ?? 'unpaid') }}
            </span>
            @if($item)
                <small class="text-muted">Paid {{ currency_symbol() }}{{ number_format($item->paid_amount, 2) }} · Due {{ currency_symbol() }}{{ number_format($item->dueAmount(), 2) }}</small>
            @endif
        </div>
        <small class="text-muted">Set automatically from this invoice's receipts.</small>
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('acc.invoice.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/pages/invoice-form.js') }}?v={{ filemtime(public_path('backend/js/custom/pages/invoice-form.js')) }}"></script>
@endpush
