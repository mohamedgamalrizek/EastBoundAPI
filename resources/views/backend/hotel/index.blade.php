@extends('backend.partials.master')
@section('title') Hotels @endsection
@section('maincontent')
<x-page title="Hotels" :breadcrumb="['Hotel','Hotels']">

    @if(hasPermission('hotel_create'))
    <x-slot name="action">
        <a href="{{ route('hotel.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon/plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Hotels overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Name','City','Country','Stars','Rooms','Rate/night','Status','Action']">
        @foreach($items as $h)
            @php $c = $h->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $h->id }}">
                <td><b>{{ $h->name }}</b></td>
                <td>{{ $h->city }}</td>
                <td>{{ $h->country }}</td>
                <td><span class="text-warning"><i class="fa fa-star"></i> {{ $h->category }}</span></td>
                <td>{{ $h->rooms_count }}</td>
                <td>{{ currency_symbol() }}{{ number_format($h->price_per_night) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($h->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('hotel.details', $h->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.view') }}"><i class="fa fa-eye"></i></a>
                        @if(hasPermission('hotel_update'))
                        <a href="{{ route('hotel.edit', $h->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('hotel_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('hotel.delete', $h->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $h->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
