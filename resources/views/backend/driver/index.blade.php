@extends('backend.partials.master')
@section('title') {{ ___('label.driver') }} @endsection
@section('maincontent')
<x-page title="Drivers" :breadcrumb="['Transport','Drivers']">

    @if(hasPermission('transport_create'))
    <x-slot name="action">
        <a href="{{ route('transport.driver.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Name','Phone','License No','Vehicle','Status','Action']">
        @foreach($items as $d)
            @php $c = $d->status === 'Active' ? 'success' : ($d->status === 'On Leave' ? 'warning' : 'danger'); @endphp
            <tr id="row_{{ $d->id }}">
                <td><b>{{ $d->name }}</b></td>
                <td>{{ $d->phone ?: '—' }}</td>
                <td>{{ $d->license_no ?: '—' }}</td>
                <td>{{ $d->vehicle ?: '—' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $d->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('transport_update'))
                        <a href="{{ route('transport.driver.edit', $d->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('transport_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('transport.driver.delete', $d->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $d->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
