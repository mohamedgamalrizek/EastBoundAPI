@extends('backend.partials.master')
@section('title') {{ $title }} @endsection
@section('maincontent')
<x-page :title="$title" :breadcrumb="['DMC', 'Reports']">
    <x-slot:action><a class="btn btn-outline-primary" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">Export CSV</a></x-slot:action>
    @if(in_array($type, ['marketing', 'sales'])) @include('backend.reports._filterbar') @endif
    <div class="row mb-3">@foreach($metrics as $label => $value)<div class="col-md-4 mb-3"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">{{ $label }}</div><h3>{{ number_format($value, 2) }}</h3></div></div></div>@endforeach</div>
    @if(isset($note))<p class="text-muted">{{ $note }}</p>@endif
    @foreach($tables ?? [] as $table)
        <h4>{{ $table['title'] }}</h4>
        <x-data-table :headers="$table['headers']">@foreach($table['rows'] as $row)<tr>@foreach($row as $value)<td>{{ $value === null ? '—' : $value }}</td>@endforeach</tr>@endforeach</x-data-table>
    @endforeach
    <x-data-table :headers="$headers">
        @foreach($rows as $row)<tr>@foreach($row as $value)<td>{{ $value === null ? '—' : $value }}</td>@endforeach</tr>@endforeach
    </x-data-table>
</x-page>
@endsection
