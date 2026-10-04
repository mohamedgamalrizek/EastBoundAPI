@extends('backend.partials.master')
@section('title') {{ ___('label.payslip') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.payslip') }}" :breadcrumb="['HR','Payslip']">

    @if(hasPermission('hr_create'))
    <x-slot name="action">
        <a href="{{ route('hr.payslip.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Staff Name','Month','Basic','Net Pay','Status','Action']">
        @foreach($items as $p)
            @php $c = $p->status === 'Paid' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $p->id }}">
                <td><b>{{ $p->staff_name }}</b></td>
                <td>{{ $p->month }}</td>
                <td>{{ currency_symbol() }}{{ number_format($p->basic) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($p->net_pay) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $p->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('hr_update'))
                        <a href="{{ route('hr.payslip.edit', $p->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('hr_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('hr.payslip.delete', $p->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $p->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
