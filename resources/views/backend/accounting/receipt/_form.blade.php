{{-- Shared Receipt form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $invoices, $customers, $methods. --}}
@php
    $receivedOn = old('received_on', $item && $item->received_on ? $item->received_on->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="receipt_no">{{ ___('label.receipt_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="receipt_no" name="receipt_no" class="form-control input-style-1" placeholder="{{ ___('label.receipt_no') }}" value="{{ old('receipt_no', $item->receipt_no ?? '') }}">
        @error('receipt_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="invoice_id">{{ ___('label.invoice') }} <span class="text-danger">*</span></label>
        <select id="invoice_id" name="invoice_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($invoices as $invoice)
                @php $due = (float) $invoice->amount - (float) $invoice->paid_amount; @endphp
                <option value="{{ $invoice->id }}"
                        data-due="{{ $due }}"
                        data-customer-id="{{ $invoice->customer_id }}"
                        data-customer-name="{{ $invoice->customer_name }}"
                        @selected(old('invoice_id', $item->invoice_id ?? '') == $invoice->id)>
                    {{ $invoice->invoice_no }} — {{ $invoice->customer_name }} (due {{ currency_symbol() }}{{ number_format($due, 2) }})
                </option>
            @endforeach
        </select>
        <small class="text-muted">A receipt cannot collect more than the invoice still owes.</small>
        @error('invoice_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_id">{{ ___('label.customer') }}</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $item->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_name">{{ ___('label.customer_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="customer_name" name="customer_name" class="form-control input-style-1" placeholder="{{ ___('label.customer_name') }}" value="{{ old('customer_name', $item->customer_name ?? '') }}">
        @error('customer_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="received_on">{{ ___('label.received_on') }} <span class="text-danger">*</span></label>
        <input type="date" id="received_on" name="received_on" class="form-control input-style-1" value="{{ $receivedOn }}">
        @error('received_on') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="amount">{{ ___('label.amount') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" id="amount" name="amount" class="form-control input-style-1" placeholder="0.00" value="{{ old('amount', $item->amount ?? '') }}">
        @error('amount') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="method">{{ ___('label.method') }} <span class="text-danger">*</span></label>
        <select id="method" name="method" class="form-control input-style-1 select2">
            @foreach($methods as $m)
                <option value="{{ $m }}" @selected(old('method', $item->method ?? 'Cash') === $m)>{{ $m }}</option>
            @endforeach
        </select>
        @error('method') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="reference">{{ ___('label.reference') }}</label>
        <input type="text" id="reference" name="reference" class="form-control input-style-1" placeholder="{{ ___('label.reference') }}" value="{{ old('reference', $item->reference ?? '') }}">
        @error('reference') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('acc.receipt.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/pages/receipt-form.js') }}?v={{ filemtime(public_path('backend/js/custom/pages/receipt-form.js')) }}"></script>
@endpush
