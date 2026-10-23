@php $quoteItems = old('items', $record->exists ? $record->items->toArray() : [[]]); @endphp
<h4>Itinerary & Pricing</h4>
<p class="text-muted">Use the company's accounting currency for all prices and costs. Prices are calculated and verified on the server.</p>
<div class="table-responsive mb-3">
    <table class="table" id="quoteItems"><thead><tr><th>Service</th><th>Description</th><th>Date</th><th>Vendor</th><th>Qty</th><th>Unit cost</th><th>Unit price</th><th></th></tr></thead>
        <tbody>
            @foreach($quoteItems as $index => $line)
                @include('backend.dmc.quotation-line', ['index' => $index, 'line' => $line])
            @endforeach
        </tbody>
    </table>
</div>
<button type="button" class="btn btn-outline-primary mb-3" id="addQuoteLine">Add service / itinerary day</button>
<p id="quoteTotals" aria-live="polite"></p>
<template id="quoteLineTemplate">@include('backend.dmc.quotation-line', ['index' => '__INDEX__', 'line' => []])</template>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('#quoteItems tbody');
    let nextIndex = {{ count($quoteItems) }};
    const totals = function () {
        let cost = 0;
        let price = 0;
        table.querySelectorAll('tr').forEach(function (row) {
            const quantity = Number(row.querySelector('[name$="[quantity]"]').value) || 0;
            cost += quantity * (Number(row.querySelector('[name$="[unit_cost]"]').value) || 0);
            price += quantity * (Number(row.querySelector('[name$="[unit_price]"]').value) || 0);
        });
        document.getElementById('quoteTotals').textContent = 'Cost: ' + cost.toFixed(2) + ' | Price: ' + price.toFixed(2) + ' | Margin: ' + (price ? ((price - cost) / price * 100).toFixed(2) : '0.00') + '%';
    };
    document.getElementById('addQuoteLine').addEventListener('click', function () {
        table.insertAdjacentHTML('beforeend', document.getElementById('quoteLineTemplate').innerHTML.replaceAll('__INDEX__', nextIndex++));
    });
    table.addEventListener('click', function (event) {
        if (event.target.matches('[data-remove-line]') && table.children.length > 1) {
            event.target.closest('tr').remove();
            totals();
        }
    });
    table.addEventListener('input', totals);
    totals();
});
</script>
@endpush
