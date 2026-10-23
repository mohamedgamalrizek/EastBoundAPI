@extends('backend.partials.master')
@section('title') {{ $definition['title'] }} @endsection
@section('maincontent')
<x-page :title="$definition['title']" :breadcrumb="['DMC', $definition['title']]">
    <x-slot:action>
        @if(($definition['editable'] || ($definition['create_only'] ?? false)) && hasPermission($definition['write_permission'] ?? $definition['permission'].'_create'))
            <a class="btn btn-primary" href="{{ route('dmc.'.$module.'.create') }}">Create {{ $definition['title'] }}</a>
        @endif
    </x-slot:action>
    @php
        $generatedColumns = [
            'sales-orders' => ['quotation_id', 'booking_id', 'customer_confirmation'],
            'trips' => ['title', 'status', 'starts_on', 'ends_on', 'manager_id'],
            'purchase-requests' => ['description', 'trip_id', 'status', 'estimated_cost', 'required_on'],
            'vendor-orders' => ['supplier_id', 'trip_id', 'amount', 'status', 'required_on'],
            'trip-items' => ['trip_id', 'service_date', 'status', 'confirmation'],
        ];
        $columns = $generatedColumns[$module] ?? array_slice(array_keys($definition['fields']), 0, 5);
    @endphp
    @if(request('view'))<p class="text-muted">View: {{ ucfirst(request('view')) }}</p>@endif
    <div class="tv-card"><div class="tv-card-body table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Reference</th>@foreach($columns as $column)<th>{{ $definition['fields'][$column]['label'] ?? \Illuminate\Support\Str::headline($column) }}</th>@endforeach<th>Details</th></tr></thead>
            <tbody>
                @forelse($records as $record)
                    <tr><td>#{{ $record->id }}</td>
                        @foreach($columns as $column)
                            <td>{{ $presenter->display($record, $column, $options[$column] ?? []) }}</td>
                        @endforeach
                        <td><a href="{{ route('dmc.'.$module.'.show', $record->id) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) + 2 }}" class="text-center text-muted py-4">No records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $records->links() }}
    </div></div>
</x-page>
@endsection
