<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>{{ $module }} #{{ $record->id }}</title><style>body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#222}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px}h1{font-size:24px}</style></head><body>
<h1>Eastbound DMC</h1>
@if($module === 'quotations')
    <h2>Quotation #{{ $record->id }} · Revision {{ $record->revision }}</h2>
    <p>{{ $record->title }} · {{ $record->opportunity->customer->name }}</p>
    <p>{{ $record->starts_on->toDateString() }} — {{ $record->ends_on->toDateString() }} · Travelers: {{ $record->travelers }} · Status: {{ $record->status }}</p>
    @if(!in_array($record->status, ['Approved', 'Accepted']))<strong>Draft / not approved for customer acceptance.</strong>@endif
    <table><thead><tr><th>Date</th><th>Itinerary / service</th><th>Quantity</th><th>Unit price</th><th>Total</th></tr></thead><tbody>@foreach($record->items as $item)<tr><td>{{ $item->service_date->toDateString() }}</td><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price, 2) }}</td><td>{{ number_format($item->unit_price * $item->quantity, 2) }}</td></tr>@endforeach</tbody></table>
    <h3>Total: {{ currency_symbol() }}{{ number_format($record->total_price, 2) }}</h3>
    <p>{!! nl2br(e($record->notes)) !!}</p>
@else
    <h2>{{ $module === 'rfqs' ? 'Request for Quotation' : 'Vendor Order' }} #{{ $record->id }}</h2>
    <p>Vendor: {{ $record->supplier->name }} · Status: {{ $record->status }}</p>
    <p>Service: {{ $record->purchaseRequest->description }} · Required: {{ $record->purchaseRequest->required_on->toDateString() }}</p>
    @if($module === 'rfqs')<p>Please quote total cost, availability and terms. Valid until: {{ $record->valid_until->toDateString() }}</p>@else<p>Agreed total: {{ currency_symbol() }}{{ number_format($record->amount, 2) }}</p>@endif
@endif
<p>All amounts use the company's base accounting currency.</p>
</body></html>
