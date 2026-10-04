@extends('backend.partials.master')
@section('title') Availability @endsection
@section('maincontent')
<x-page title="Availability" :breadcrumb="['Hotel','Availability']">

    <x-data-table :headers="['Hotel','City','Room Type','Total Rooms','Available','Occupied','Rate/night','Status']">
        @foreach($rooms as $r)
            @php
                $occupied = max(0, $r->total_rooms - $r->available_rooms);
                $c = $r->available_rooms === 0 ? 'danger' : ($r->available_rooms <= 5 ? 'warning' : 'success');
                $label = $r->available_rooms === 0 ? 'Full' : ($r->available_rooms <= 5 ? 'Low' : 'Available');
            @endphp
            <tr>
                <td><b>{{ $r->hotel?->name ?? '—' }}</b></td>
                <td>{{ $r->hotel?->city ?? '—' }}</td>
                <td>{{ $r->room_type }}</td>
                <td>{{ $r->total_rooms }}</td>
                <td>{{ $r->available_rooms }}</td>
                <td>{{ $occupied }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->rate_per_night) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $label }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
