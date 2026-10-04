@extends('backend.partials.master')
@section('title') {{ ___('menus.agent_bookings') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.agent_bookings') }}" :breadcrumb="[___('menus.agent_portal'), ___('permissions.bookings')]">

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('agent.booking.create') }}" class="j-td-btn"><i class="fa fa-plus"></i> {{ ___('menus.new_booking') }}</a>
    </div>

    <x-data-table :headers="[___('label.booking'), ___('label.customer'), ___('label.phone'), ___('label.travel_date'), ___('menus.travelers'), ___('label.amount'), ___('label.status'), '']">
        @foreach($bookings as $b)
            <tr>
                <td><b>BKG-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</b></td>
                <td>{{ $b->customer_name }}</td>
                <td>{{ $b->customer_phone }}</td>
                <td>{{ $b->travel_date?->format('d M Y') }}</td>
                <td>{{ $b->travelers }}</td>
                <td>{{ currency_symbol() }}{{ number_format($b->amount) }}</td>
                <td>{!! $b->statusBadge() !!}</td>
                <td>
                    <a href="{{ route('agent.booking.show', $b->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-eye"></i>
                    </a>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
