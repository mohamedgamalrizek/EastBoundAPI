@extends('backend.partials.master')
@section('title') {{ $definition['title'] }} #{{ $record->id }} @endsection
@section('maincontent')
<x-page :title="$definition['title'].' #'.$record->id" :breadcrumb="['DMC', $definition['title']]">
    <x-slot:action>
        @if(in_array($module, ['quotations', 'rfqs', 'vendor-orders']))<a class="btn btn-outline-primary" href="{{ route('dmc.document.'.$module, $record->id) }}">Download PDF</a>@endif
        <a class="btn btn-light" href="{{ route('dmc.'.$module.'.index') }}">Back to list</a>
        @if($canEdit && hasPermission($definition['write_permission'] ?? $definition['permission'].'_update'))
            <a class="btn btn-outline-primary" href="{{ route('dmc.'.$module.'.edit', $record->id) }}">Edit</a>
        @endif
    </x-slot:action>
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    <div class="tv-card mb-4"><div class="tv-card-body table-responsive"><table class="table">
        @foreach($record->getAttributes() as $field => $raw)
            @if(!in_array($field, ['updated_at', 'series', 'idempotency_key']) && (!isset($definition['fields'][$field]['permission']) || hasPermission($definition['fields'][$field]['permission'])))
                <tr><th>{{ $definition['fields'][$field]['label'] ?? \Illuminate\Support\Str::headline($field) }}</th><td>{{ $presenter->display($record, $field, $options[$field] ?? []) }}</td></tr>
            @endif
        @endforeach
    </table></div></div>
    @if($record instanceof \App\Models\Dmc\Quotation)
        @if($record->status === 'Submitted' && $record->created_by === auth()->id())<div class="alert alert-info">A different authorized employee must approve your quotation.</div>@endif
        <div class="tv-card mb-4"><div class="tv-card-body table-responsive"><table class="table"><thead><tr><th>Service</th><th>Date</th><th>Description</th><th>Quantity</th><th>Unit cost</th><th>Unit price</th></tr></thead><tbody>
            @foreach($record->items as $item)<tr><td>{{ $item->service_type }}</td><td>{{ $item->service_date->toDateString() }}</td><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit_cost }}</td><td>{{ $item->unit_price }}</td></tr>@endforeach
        </tbody></table></div></div>
    @endif
    @if($record instanceof \App\Models\Dmc\Trip)
        <div class="mb-4">
            <a class="btn btn-outline-primary" href="{{ route('dmc.trip-items.index', ['trip' => $record->id]) }}">Services & Resources</a>
            @if(hasPermission('supplier_read'))<a class="btn btn-outline-primary" href="{{ route('dmc.purchase-requests.index', ['trip' => $record->id]) }}">Purchase Requests</a>@endif
            <a class="btn btn-outline-primary" href="{{ route('dmc.trip-documents', $record->id) }}">Trip Documents & Vouchers</a>
        </div>
    @endif
    @if($record instanceof \App\Models\Dmc\SalesOrder && $record->trip)
        <a class="btn btn-outline-primary mb-4" href="{{ route('dmc.trips.show', $record->trip->id) }}">Open Operational Trip</a>
    @endif
    @if($record instanceof \App\Models\Dmc\Opportunity && !in_array($record->stage, ['Won', 'Lost']) && hasPermission('dmc_sales_create'))
        <a class="btn btn-primary mb-4" href="{{ route('dmc.quotations.create', ['opportunity_id' => $record->id]) }}">Create Quotation</a>
    @endif
    @if($record instanceof \App\Models\Dmc\PurchaseRequest && $record->status !== 'Ordered' && hasPermission('dmc_procurement_create'))
        <a class="btn btn-primary mb-4" href="{{ route('dmc.rfqs.create', ['purchase_request_id' => $record->id]) }}">Create Vendor RFQ</a>
    @endif
    @if($record instanceof \App\Models\Dmc\VendorOrder && hasPermission('accounting_create'))
        <a class="btn btn-primary mb-4" href="{{ route('dmc.vendor-bills.create', ['vendor_order_id' => $record->id]) }}">Register Vendor Bill</a>
    @endif
    @if($record instanceof \App\Models\Dmc\VendorBill)
        <h4>Payments & Outstanding Balance</h4>
        <p>Outstanding: {{ number_format(max(0, $record->amount - $record->payments()->sum('amount')), 2) }}</p>
        <table class="table"><thead><tr><th>Paid on</th><th>Reference</th><th>Method</th><th>Amount</th></tr></thead><tbody>@foreach($record->payments as $payment)<tr><td>{{ $payment->paid_on->toDateString() }}</td><td>{{ $payment->reference }}</td><td>{{ $payment->method }}</td><td>{{ number_format($payment->amount, 2) }}</td></tr>@endforeach</tbody></table>
        @if(hasPermission('accounting_create'))<a class="btn btn-primary mb-4" href="{{ route('dmc.vendor-payments.create', ['vendor_bill_id' => $record->id]) }}">Record Vendor Payment</a>@endif
    @endif
    @if($record instanceof \App\Models\Dmc\TripItem)
        <p>{{ $record->quotationItem->description }} — {{ $record->quotationItem->service_type }}</p>
    @endif
    @php
        $actions = match ($module) {
            'quotations' => match ($record->status) { 'Draft' => ['submit'], 'Submitted' => ['approve', 'reject'], 'Approved' => ['accept', 'revise'], 'Rejected' => ['revise'], default => [] },
            'rfqs' => match ($record->status) { 'Draft' => ['send'], 'Sent' => ['respond'], 'Responded' => ['award'], default => [] },
            'trips' => match ($record->status) { 'Planning' => ['ready'], 'Ready' => ['start'], 'In Progress' => ['complete'], default => [] },
            'trip-items' => $record->status === 'Planned' ? ['confirm'] : [],
            'opportunities' => in_array($record->stage, ['Won', 'Lost']) ? [] : ['lose'],
            'vendor-orders' => $record->status === 'Issued' ? ['receive'] : [],
            default => [],
        };
    @endphp
    @foreach($actions as $action)
        @php $approval = in_array($action, ['approve', 'reject']); @endphp
        @if(($approval ? (hasPermission('dmc_sales_approve_gm') || hasPermission('dmc_sales_approve_tl')) : (hasPermission($definition['permission'].'_update') || ($action === 'award' && hasPermission('dmc_sales_approve_gm')))) && (!$approval || $record->created_by !== auth()->id()))
            <form method="POST" action="{{ route('dmc.'.$module.'.action', $record->id) }}" class="tv-card mb-3"><div class="tv-card-body">
                @csrf <input type="hidden" name="action" value="{{ $action }}">
                @if($approval || $action === 'lose')<label>Reason / audit note</label><textarea name="note" class="form-control mb-2" required maxlength="2000"></textarea>@endif
                @if($action === 'award')<label>Budget variance reason (General Manager authorization required above approved cost)</label><textarea name="note" class="form-control mb-2" maxlength="2000"></textarea>@endif
                @if($action === 'accept')<label>Customer acceptance reference (email, signed confirmation)</label><input name="customer_confirmation" class="form-control mb-2" required maxlength="255">@endif
                @if($action === 'accept')<label>Operations manager</label><select name="manager_id" class="form-control mb-2" required><option value="">Select operations manager</option>@foreach($operationsManagers as $manager)<option value="{{ $manager->id }}" @selected($manager->id === auth()->id())>{{ $manager->name }}</option>@endforeach</select>@endif
                <button type="submit" class="btn btn-primary">{{ $action === 'send' ? 'Mark RFQ as Sent' : \Illuminate\Support\Str::headline($action) }}</button>
            </div></form>
        @endif
    @endforeach
</x-page>
@endsection
