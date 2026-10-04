@extends('backend.partials.master')
@section('title') Student Consultancy @endsection
@section('maincontent')
<x-page title="Student Consultancy" :breadcrumb="['Student Consultancy']">

    @if(hasPermission('student_service_create'))
    <x-slot name="action">
        <a href="{{ route('student-service.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Student Consultancy overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Student Name','University','Country','Service Type','Customer','Service Fee','Status','Action']">
        @foreach($items as $r)
            @php $sc = \Illuminate\Support\Str::contains(strtolower($r->status), ['cancel','reject','inactive','closed','paused','fail']) ? 'danger' : (\Illuminate\Support\Str::contains(strtolower($r->status), ['pend','process','plan','inquiry','open']) ? 'warning' : 'success'); @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->student_name }}</b></td>
                <td>{{ $r->university }}</td>
                <td>{{ $r->country }}</td>
                <td>{{ $r->service_type }}</td>
                <td>
                    @if($r->customer)
                        <a href="{{ route('customer.show', $r->customer_id) }}">{{ $r->customer->name }}</a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>{{ $r->service_fee !== null ? currency_symbol() . number_format($r->service_fee) : '—' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $r->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('student_service_update'))
                        <a href="{{ route('student-service.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('student_service_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('student-service.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="Delete" data-text="This action cannot be reversed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-page>
@endsection
