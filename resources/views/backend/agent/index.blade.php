@extends('backend.partials.master')
@section('title') {{ ___('label.agent') }} @endsection
@section('maincontent')
<x-page title="Agents" :breadcrumb="['Agent','Agents']">

    {{-- Agents are Users carrying the "Agent" role, so they are created and
         deactivated in Users & Roles — this roster stays read-only. --}}
    @if(hasPermission('user_create'))
    <x-slot name="action">
        <a href="{{ route('user.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Agent Overview" :stats="$analytics['stats']" />
    @endif

    <x-data-table :headers="['Agent','Contact','Bookings','Sales','Commission','Pending','Wallet','Status','Action']">
        @foreach($agents as $a)
            <tr id="row_{{ $a->id }}">
                <td>
                    <b>{{ $a->name }}</b>
                    @if($a->stat_unpaid_inv)
                        <span class="bullet-badge bullet-badge-warning ml-1">{{ $a->stat_unpaid_inv }} unpaid</span>
                    @endif
                </td>
                <td>
                    {{ $a->email }}
                    @if($a->phone)<br><small class="text-muted">{{ $a->phone }}</small>@endif
                </td>
                <td>{{ number_format($a->stat_bookings) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($a->stat_sales) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($a->stat_commission) }}</td>
                <td>
                    @if($a->stat_pending > 0)
                        <span class="text-warning">{{ currency_symbol() }}{{ number_format($a->stat_pending) }}</span>
                    @else
                        —
                    @endif
                </td>
                <td>{{ currency_symbol() }}{{ number_format($a->stat_wallet) }}</td>
                <td>{!! @$a->MyStatus !!}</td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('user_update'))
                        <a href="{{ route('user.edit', $a->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('agent_finance_read'))
                        <a href="{{ route('agent.commission.index') }}" class="btn btn-sm btn-outline-primary" title="Commissions"><i class="fa fa-percent"></i></a>
                        <a href="{{ route('agent.invoice.index') }}" class="btn btn-sm btn-outline-primary" title="Invoices"><i class="fa fa-file-invoice"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
