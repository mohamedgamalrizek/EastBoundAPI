@extends('backend.partials.master')
@section('title') {{ ___('label.agent') }} {{ ___('label.invoice') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.invoice') }}" :breadcrumb="['Agent','Invoices']">

    @if(hasPermission('agent_finance_create'))
    <x-slot name="action">
        <a href="{{ route('agent.invoice.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Invoice No','Customer','Amount','Issued On','Due On','Status','Settled','Action']">
        @foreach($items as $r)
            @php $c = $r->status === 'paid' ? 'success' : ($r->status === 'overdue' ? 'danger' : 'warning'); @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->invoice_no }}</b></td>
                <td>{{ $r->customer_name }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->amount, 2) }}</td>
                <td>{{ $r->issued_on?->format('Y-m-d') }}</td>
                <td>{{ $r->due_on?->format('Y-m-d') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($r->status) }}</span></td>
                <td>{{ $r->status === 'paid' && $r->method ? $r->method . ' · ' . $r->paid_on?->format('Y-m-d') : '—' }}</td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('agent_finance_update'))
                        <a href="{{ route('agent.invoice.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('agent_finance_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('agent.invoice.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
