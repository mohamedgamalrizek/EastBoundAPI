@extends('backend.partials.master')
@section('title') Visa Applications @endsection
@section('maincontent')
<x-page title="Applications" :breadcrumb="['Visa','Applications']">

    @if(hasPermission('visa_create'))
    <x-slot name="action">
        <a href="{{ route('visa.application.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    <x-list-analytics
        title="Visa applications overview"
        :stats="$analytics['stats']"
        :donut="$analytics['donut']"
        :trend="$analytics['trend']" />

    <x-data-table :headers="['ID','Applicant','Country','Type','Submitted','Docs','Status','Action']">
        @foreach($applications as $a)
            @php
                $c  = $a->status === 'Approved' ? 'success' : ($a->status === 'Rejected' ? 'danger' : 'warning');
                $dc = $a->documents_status === 'Verified' ? 'success' : ($a->documents_status === 'Submitted' ? 'info' : 'warning');
            @endphp
            <tr id="row_{{ $a->id }}">
                <td><b>{{ $a->application_no }}</b></td>
                <td>{{ $a->applicant_name }}</td>
                <td>{{ $a->country }}</td>
                <td>{{ $a->visa_type }}</td>
                <td>{{ $a->applied_date?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $dc }}">{{ $a->documents_status }}</span></td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $a->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('visa.application.details', $a->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.view') }}"><i class="fa fa-eye"></i></a>
                        @if(hasPermission('visa_update'))
                        <a href="{{ route('visa.application.edit', $a->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('visa_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('visa.application.delete', $a->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $a->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
