@extends('backend.partials.master')
@section('title') {{ ___('label.announcements') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.announcements') }}" :breadcrumb="['Support','Announcements']">

    @if(hasPermission('support_create'))
    <x-slot name="action">
        <a href="{{ route('support.announcement.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Title','Audience','Published On','Status','Action']">
        @foreach($items as $a)
            @php $c = $a->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $a->id }}">
                <td><b>{{ $a->title }}</b></td>
                <td>{{ $a->audience }}</td>
                <td>{{ $a->published_on ? $a->published_on->format('d M Y') : '-' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($a->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('support_update'))
                        <a href="{{ route('support.announcement.edit', $a->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('support_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('support.announcement.delete', $a->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $a->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
