@php $booking = $booking ?? null; @endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Customer <span class="text-danger">*</span></label>
        @php $selCustomer = old('customer_id', $booking->customer_id ?? ''); @endphp
        {{-- Picks from the Customers CRUD so the booking is linked by
             customer_id. A typed name would leave it NULL and the record
             would never show up in the customer's own portal. --}}
        <select name="customer_id" id="bookingCustomer" class="form-control input-style-1 select2">
            <option value="">-- Select customer --</option>
            @foreach($customers as $cust)
            <option value="{{ $cust->id }}"
                    data-email="{{ $cust->email }}"
                    data-phone="{{ $cust->phone }}"
                    @selected((string) $selCustomer === (string) $cust->id)>
                {{ $cust->name }}@if($cust->phone) - {{ $cust->phone }}@endif
            </option>
            @endforeach
        </select>
        @error('customer_id') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
        @if(hasPermission('customer_create'))
        <small class="d-block pt-2">
            Not in the list? <a href="{{ route('customer.create') }}" target="_blank">Add a customer</a>
        </small>
        @endif
    </div>

    <div class="form-group col-md-6">
        <label>Booked by (Agent)</label>
        @php $selAgent = old('agent_id', $booking->agent_id ?? ''); @endphp
        {{-- The agent portal shows an agent only their own production, and it
             finds it through this column — a booking saved without it is
             invisible to the agent who made it. --}}
        <select name="agent_id" class="form-control input-style-1 select2">
            <option value="">-- Direct / office booking --</option>
            @foreach($agents as $agent)
            <option value="{{ $agent->id }}" @selected((string) $selAgent === (string) $agent->id)>
                {{ $agent->name }}@if($agent->email) - {{ $agent->email }}@endif
            </option>
            @endforeach
        </select>
        @error('agent_id') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Package</label>
        @php $selPkg = old('package_id', $booking->package_id ?? ''); @endphp
        <select name="package_id" class="form-control input-style-1 select2">
            <option value="">-- No package --</option>
            @foreach($packages as $pkg)
            <option value="{{ $pkg->id }}" @selected((string)$selPkg === (string)$pkg->id)>{{ $pkg->title }} ({{ $pkg->destination }})</option>
            @endforeach
        </select>
        @error('package_id') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Customer Email</label>
        <input type="email" name="customer_email" class="form-control input-style-1" placeholder="you@email.com"
            value="{{ old('customer_email', $booking->customer_email ?? '') }}">
        @error('customer_email') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Customer Phone</label>
        <input type="text" name="customer_phone" class="form-control input-style-1" placeholder="+880 17..."
            value="{{ old('customer_phone', $booking->customer_phone ?? '') }}">
        @error('customer_phone') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-4">
        <label>Travel Date <span class="text-danger">*</span></label>
        <input type="date" name="travel_date" class="form-control input-style-1"
            value="{{ old('travel_date', $booking?->travel_date?->format('Y-m-d') ?? '') }}">
        @error('travel_date') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-4">
        <label>Travelers <span class="text-danger">*</span></label>
        <input type="number" name="travelers" class="form-control input-style-1" min="1"
            value="{{ old('travelers', $booking->travelers ?? 1) }}">
        @error('travelers') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-4">
        <label>Amount ({{ currency_symbol() }}) <span class="text-danger">*</span></label>
        {{-- The list price. A promo code comes off it, so what the customer
             owes is this figure minus the discount, not this figure. --}}
        <input type="number" step="0.01" name="amount" class="form-control input-style-1" placeholder="115000"
            value="{{ old('amount', isset($booking) ? $booking->grossAmount() : '') }}">
        <small class="d-block pt-2 text-muted">Price before any promo code.</small>
        @error('amount') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-4">
        <label>Promo code</label>
        <input type="text" name="coupon_code" class="form-control input-style-1" placeholder="e.g. SUMMER20"
            value="{{ old('coupon_code', $booking->coupon_code ?? '') }}" autocomplete="off">
        @if(isset($booking) && $booking->hasDiscount())
            <small class="d-block pt-2 text-success">
                Currently &minus;{{ currency_symbol() }}{{ number_format($booking->totalDiscount(), 2) }}
                @if((float) $booking->points_discount > 0)
                    (incl. {{ number_format($booking->points_redeemed) }} loyalty points)
                @endif
                &rarr; payable {{ currency_symbol() }}{{ number_format((float) $booking->amount, 2) }}
            </small>
        @else
            <small class="d-block pt-2 text-muted">An unusable code is ignored and reported; it never blocks the save.</small>
        @endif
        @error('coupon_code') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Payment method</label>
        @php $selMethod = old('payment_method', $booking->payment_method ?? ''); @endphp
        {{-- Used when the booking is marked paid: the receipt is recorded
             against this account, and Wallet spends the customer's balance. --}}
        <select name="payment_method" class="form-control input-style-1 select2">
            <option value="">-- Not paid yet --</option>
            @foreach(['Cash','Bank','Card','bKash','Nagad','Wallet'] as $pm)
            <option value="{{ $pm }}" @selected((string) $selMethod === $pm)>{{ $pm }}</option>
            @endforeach
        </select>
        <small class="d-block pt-2 text-muted">Marking a booking <b>paid</b> raises its invoice and records the receipt automatically.</small>
        @error('payment_method') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Status <span class="text-danger">*</span></label>
        @php $selStatus = old('status', $booking->status ?? 'pending'); @endphp
        <select name="status" class="form-control input-style-1 select2">
            @foreach(['pending','confirmed','paid','cancelled'] as $st)
            <option value="{{ $st }}" @selected($selStatus === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-12">
        <label>Notes</label>
        <textarea name="notes" rows="3" class="form-control input-style-1" placeholder="Internal notes...">{{ old('notes', $booking->notes ?? '') }}</textarea>
        @error('notes') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/pages/booking-form.js') }}?v={{ filemtime(public_path('backend/js/custom/pages/booking-form.js')) }}"></script>
@endpush
