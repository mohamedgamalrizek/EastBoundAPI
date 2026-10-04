@extends('backend.partials.master')
@section('title') Supplier Ledger @endsection
@section('maincontent')
<x-page title="Supplier Ledger" :breadcrumb="['Suppliers','Ledger']">

    {{-- Pick a supplier to see its statement; the totals above always reflect
         the current filter so a period can be reconciled on its own. --}}
    <div class="tv-card mb-3"><div class="tv-card-body">
        <form action="{{ route('sup.ledger') }}" method="get" class="form-row align-items-end">
            <div class="form-group col-md-4">
                <label class="label-style-1" for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id" class="form-control input-style-1 select2" onchange="this.form.submit()">
                    <option value="">All suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected((int) request('supplier_id') === $s->id)>
                            {{ $s->name }} — {{ currency_symbol() }}{{ number_format((float) $s->balance) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="label-style-1" for="from">From</label>
                <input type="date" id="from" name="from" class="form-control input-style-1" value="{{ request('from') }}">
            </div>
            <div class="form-group col-md-3">
                <label class="label-style-1" for="to">To</label>
                <input type="date" id="to" name="to" class="form-control input-style-1" value="{{ request('to') }}">
            </div>
            <div class="form-group col-md-2">
                <button type="submit" class="j-td-btn btn-block">{{ ___('label.filter') }}</button>
            </div>
        </form>
    </div></div>

    <div class="row mb-1">
        <div class="col-md-4 col-6 mb-3">
            <div class="tv-card h-100"><div class="tv-card-body">
                <div class="text-muted tv-text-xs">Billed{{ $selected ? '' : ' (all suppliers)' }}</div>
                <h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($totalBilled) }}</h3>
            </div></div>
        </div>
        <div class="col-md-4 col-6 mb-3">
            <div class="tv-card h-100"><div class="tv-card-body">
                <div class="text-muted tv-text-xs">Paid</div>
                <h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($totalPaid) }}</h3>
            </div></div>
        </div>
        <div class="col-md-4 col-12 mb-3">
            <div class="tv-card h-100"><div class="tv-card-body">
                <div class="text-muted tv-text-xs">
                    {{ $selected ? 'Outstanding — ' . $selected->name : 'Total payables' }}
                </div>
                <h3 class="mb-0 text-danger tv-fw-700">
                    {{ currency_symbol() }}{{ number_format($selected ? (float) $selected->balance : $totalBalance) }}
                </h3>
            </div></div>
        </div>
    </div>

    @if(hasPermission('supplier_create'))
    {{-- Recording an entry is the whole point of a ledger, so the form lives
         on the page rather than behind another screen. --}}
    <div class="tv-card mb-3"><div class="tv-card-body">
        <h6 class="mb-3">Record an entry</h6>
        <form action="{{ route('sup.ledger.store') }}" method="POST" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-3">
                <label class="label-style-1" for="e_supplier">Supplier <span class="text-danger">*</span></label>
                <select id="e_supplier" name="supplier_id" class="form-control input-style-1 select2">
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected((int) old('supplier_id', request('supplier_id')) === $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="form-group col-md-2">
                <label class="label-style-1" for="e_type">Type <span class="text-danger">*</span></label>
                <select id="e_type" name="type" class="form-control input-style-1 select2">
                    @foreach($txnTypes as $type)
                        <option value="{{ $type }}" @selected(old('type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                {{-- Only a payment moves money, and the books need to know
                     which account it left. --}}
                <label class="label-style-1" for="e_method">Paid from</label>
                <select id="e_method" name="method" class="form-control input-style-1">
                    @foreach(\App\Models\SupplierTransaction::METHODS as $m)
                        <option value="{{ $m }}" @selected(old('method') === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-2">
                <label class="label-style-1" for="e_date">Date <span class="text-danger">*</span></label>
                <input type="date" id="e_date" name="txn_date" class="form-control input-style-1" value="{{ old('txn_date', date('Y-m-d')) }}">
                @error('txn_date') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="form-group col-md-2">
                <label class="label-style-1" for="e_amount">Amount <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0.01" id="e_amount" name="amount" class="form-control input-style-1" placeholder="0.00" value="{{ old('amount') }}">
                @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="form-group col-md-2">
                <label class="label-style-1" for="e_ref">Reference</label>
                <input type="text" id="e_ref" name="reference" class="form-control input-style-1" placeholder="INV-1042" value="{{ old('reference') }}">
            </div>
            <div class="form-group col-md-1">
                <button type="submit" class="j-td-btn btn-block">{{ ___('label.save') }}</button>
            </div>
            <div class="form-group col-md-12 mb-0">
                <input type="text" name="description" class="form-control input-style-1" placeholder="What is this entry for?" value="{{ old('description') }}">
            </div>
        </form>
    </div></div>
    @endif

    <x-data-table :headers="['Date','Supplier','Type','Reference','Billed','Paid','Balance','Action']">
        @forelse($entries as $e)
            @php $isCredit = (float) $e->credit > 0; @endphp
            <tr id="row_{{ $e->id }}">
                <td>{{ $e->txn_date?->format('d M Y') }}</td>
                <td>
                    {{ $e->supplier->name ?? '—' }}
                    @if($e->description)<div class="text-muted tv-text-2xs">{{ $e->description }}</div>@endif
                </td>
                <td><span class="bullet-badge bullet-badge-{{ $isCredit ? 'warning' : 'success' }}">{{ $e->type }}</span></td>
                <td>{{ $e->reference ?: '—' }}</td>
                <td>{{ $isCredit ? currency_symbol() . number_format((float) $e->credit) : '—' }}</td>
                <td>{{ ! $isCredit ? currency_symbol() . number_format((float) $e->debit) : '—' }}</td>
                <td><b>{{ currency_symbol() }}{{ number_format((float) $e->balance_after) }}</b></td>
                <td>
                    @if(hasPermission('supplier_delete'))
                    <a class="text-danger" href="{{ route('sup.ledger.delete', $e->id) }}" onclick="tryDelete(event)"
                       data-remove-id="row_{{ $e->id }}" data-title="{{ ___('label.delete') }}"
                       data-text="The running balance will be recalculated."
                       data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"
                       data-reload="true"><i class="fa fa-trash"></i></a>
                    @endif
                </td>
            </tr>
        @empty
            <x-nodata-found :colspan="8" />
        @endforelse
    </x-data-table>

</x-page>
@endsection
