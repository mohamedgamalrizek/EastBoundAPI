@extends('backend.partials.master')
@section('title') Job Openings @endsection
@section('maincontent')
<x-page title="Job Openings" :breadcrumb="['CMS','Careers']">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.job-opening.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Position','Department','Location','Type','Closes','Status','Action']">
        @foreach($items as $item)
            @php
                $c      = $item->status === 'active' ? 'success' : 'danger';
                $closed = $item->closing_date && $item->closing_date->isPast();
            @endphp
            <tr id="row_{{ $item->id }}">
                <td><b>{{ $item->title }}</b></td>
                <td>{{ $item->department ?: '—' }}</td>
                <td>{{ $item->location ?: '—' }}</td>
                <td>{{ $item->employment_type }}</td>
                <td>
                    {{ $item->closing_date ? dateFormat($item->closing_date) : '—' }}
                    @if($closed)<span class="bullet-badge bullet-badge-warning ms-1">Expired</span>@endif
                </td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($item->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.job-opening.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.job-opening.delete', $item->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $item->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
