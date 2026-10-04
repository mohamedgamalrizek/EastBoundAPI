@extends('backend.partials.master')
@section('title') Transport @endsection
@section('maincontent')
<x-page title="Transport" :breadcrumb="['Transport','Bookings']">

    @if(hasPermission('transport_create'))
    <x-slot name="action">
        <a href="{{ route('transport.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Transport bookings overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Booking No','Customer','Type','Route','Travel Date','Vehicle','Driver','Fare','Status','Action']">
        @foreach($items as $t)
            <tr id="row_{{ $t->id }}">
                <td><b>{{ $t->booking_no }}</b></td>
                <td>{{ $t->customer_name }}</td>
                <td>{{ $t->type }}{{ $t->direction ? ' — ' . $t->direction : '' }}</td>
                <td>{{ $t->route }}</td>
                <td>{{ optional($t->travel_date)->format('Y-m-d') }}</td>
                <td>{{ $t->vehicle }}</td>
                <td>{{ $t->driver->name ?? '—' }}</td>
                <td>{{ currency_symbol() }}{{ number_format($t->fare) }}</td>
                <td>
                    {{ $t->status }}
                    @if($t->payment_claimed_at)
                        <br><small class="text-muted" title="{{ $t->payment_claimed_at }}">Payment claimed</small>
                    @endif
                </td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('transport.show', $t->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.view') }}"><i class="fa fa-eye"></i></a>
                        @if(hasPermission('transport_update'))
                        <a href="{{ route('transport.edit', $t->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('transport_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('transport.delete', $t->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $t->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
