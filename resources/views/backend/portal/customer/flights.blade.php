@extends('backend.partials.master')
@section('title') {{ ___('menus.flight_tickets') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.flight_tickets') }}" :breadcrumb="['Customer Portal', ___('menus.flight_tickets')]">

    <x-data-table :headers="[___('label.ticket'), ___('label.route'), ___('label.airline'), ___('label.date'), ___('label.status')]">
        @foreach($flights as $f)
            @php
                $map = ['Issued'=>'success','Confirmed'=>'info','Used'=>'info','Cancelled'=>'danger'];
                $c = $map[$f->status] ?? 'warning';
            @endphp
            <tr>
                <td><b>{{ $f->ticket_no ?? $f->pnr }}</b></td>
                <td>{{ $f->route }}</td>
                <td>{{ $f->airline }}</td>
                <td>{{ $f->flight_date?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $f->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
