<div class="tv-card mb-4"><div class="tv-card-head"><h4>Customer 360° — Trips & Relationships</h4></div><div class="tv-card-body">
    <h5>Travel preferences</h5>
    @if(hasPermission('customer_update'))
        <form method="POST" action="{{ route('dmc.customer.preferences', $customer->id) }}" class="mb-4">@csrf @method('PUT')
            <div class="form-row">@foreach(['dietary' => 'Dietary preferences', 'accessibility' => 'Accessibility requirements', 'interests' => 'Travel interests'] as $key => $label)
                @php $preference = old($key, $customer->preferences['dmc'][$key] ?? $customer->preferences[$key] ?? ''); @endphp
                <div class="form-group col-md-4"><label>{{ $label }}</label><textarea name="{{ $key }}" maxlength="2000" class="form-control">{{ is_array($preference) ? json_encode($preference, JSON_UNESCAPED_UNICODE) : $preference }}</textarea></div>@endforeach</div>
            <button class="btn btn-outline-primary" type="submit">Save preferences</button>
        </form>
    @else
        @foreach(($customer->preferences['dmc'] ?? $customer->preferences ?? []) as $key => $value)<p>{{ \Illuminate\Support\Str::headline($key) }}: {{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</p>@endforeach
    @endif
    <h5>Operational trips</h5>
    <table class="table"><thead><tr><th>Trip</th><th>Dates</th><th>Status</th></tr></thead><tbody>@foreach($dmcTrips as $trip)<tr><td>@if(hasPermission('dmc_operations_read'))<a href="{{ route('dmc.trips.show', $trip->id) }}">{{ $trip->title }}</a>@else{{ $trip->title }}@endif</td><td>{{ $trip->starts_on->toDateString() }} — {{ $trip->ends_on->toDateString() }}</td><td>{{ $trip->status }}</td></tr>@endforeach</tbody></table>
    <h5>Previous package bookings</h5>
    <table class="table"><thead><tr><th>Booking</th><th>Programme</th><th>Date</th><th>Status</th><th>Amount</th></tr></thead><tbody>@foreach($customerBookings as $booking)<tr><td>#{{ $booking->id }}</td><td>{{ $booking->package?->title }}</td><td>{{ $booking->travel_date?->toDateString() }}</td><td>{{ $booking->status }}</td><td>{{ number_format($booking->amount, 2) }}</td></tr>@endforeach</tbody></table>
    <h5>Invoices</h5>
    <table class="table"><thead><tr><th>Invoice</th><th>Due</th><th>Amount</th><th>Collected</th><th>Outstanding</th></tr></thead><tbody>@foreach($customerInvoices as $invoice)<tr><td>{{ $invoice->invoice_no }}</td><td>{{ $invoice->due_date?->toDateString() }}</td><td>{{ number_format($invoice->amount, 2) }}</td><td>{{ number_format($invoice->paid_amount, 2) }}</td><td>{{ number_format($invoice->dueAmount(), 2) }}</td></tr>@endforeach</tbody></table>
    <h5>Contacts</h5>
    @foreach($dmcContacts as $contact)<p>{{ $contact->name }} · {{ $contact->position }} · {{ $contact->email }} · {{ $contact->phone }}</p>@endforeach
</div></div>
