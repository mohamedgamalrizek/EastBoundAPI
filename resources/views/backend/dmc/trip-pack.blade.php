<h2>{{ $trip->title }}</h2>
<p>Trip #{{ $trip->id }} · {{ $trip->customer->name }} · {{ $trip->starts_on->toDateString() }} — {{ $trip->ends_on->toDateString() }}</p>
<p>Status: {{ $trip->status }}. Only confirmed services constitute issued vouchers; other entries are planning documents.</p>
@foreach($trip->items as $item)
    <div class="tv-card mb-3"><div class="tv-card-body">
        <h3>{{ $item->status === 'Confirmed' ? 'Service Voucher' : 'Planning Record' }} #{{ $trip->id }}-{{ $item->id }}</h3>
        <p>{{ $item->service_date->toDateString() }} · {{ $item->quotationItem->description }} · Travelers / units: {{ $item->quotationItem->quantity }}</p>
        <p>Service: {{ $item->quotationItem->service_type }} · Status: {{ $item->status }} · Confirmation: {{ $item->confirmation ?: 'Not confirmed' }}</p>
        <p>Vendor: {{ $item->supplier?->name ?: 'Not assigned' }} · Resource: {{ $item->hotel?->name ?? $item->driver?->name ?? $item->guide?->name ?? $item->activity?->name ?? 'External confirmation' }}</p>
        @if($item->notes)<p>{{ $item->notes }}</p>@endif
    </div></div>
@endforeach
