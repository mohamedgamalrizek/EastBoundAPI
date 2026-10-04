@extends('backend.partials.master')
@section('title') Agent Payouts @endsection
@section('maincontent')
<x-page title="Agent Payouts" :breadcrumb="['Agent','Payouts']">

    @if(hasPermission('agent_finance_create'))
    <x-slot name="action">
        <a href="{{ route('agent.withdrawal.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    {{-- Marking a payout Paid is the only step that moves money: it debits the
         agent's wallet and posts Dr Agent Payable / Cr Cash-or-Bank. --}}
    <x-data-table :headers="['Reference','Agent','Requested','Amount','Method','Account','Status','Processed','Action']">
        @foreach($items as $w)
            @php
                $badge = match($w->status) {
                    'paid'     => 'success',
                    'approved' => 'info',
                    'rejected' => 'danger',
                    default    => 'warning',
                };
            @endphp
            <tr id="row_{{ $w->id }}">
                <td><b>{{ $w->reference }}</b></td>
                <td>{{ $w->agent->name ?? '—' }}</td>
                <td>{{ $w->requested_on?->format('d M Y') }}</td>
                <td>{{ currency_symbol() }}{{ number_format($w->amount, 2) }}</td>
                <td>{{ $w->method }}</td>
                <td><small>{{ $w->account_details }}</small></td>
                <td><span class="bullet-badge bullet-badge-{{ $badge }}">{{ ucfirst($w->status) }}</span></td>
                <td>
                    @if($w->processed_on)
                        {{ $w->processed_on->format('d M Y') }}
                        <br><small class="text-muted">{{ $w->processedBy->name ?? '' }}</small>
                    @else
                        —
                    @endif
                </td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('agent_finance_update'))
                            @if($w->status === 'requested')
                            <form method="POST" action="{{ route('agent.withdrawal.approve', $w->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-info" title="Accept the request"><i class="fa fa-check"></i> Approve</button>
                            </form>
                            @endif
                            @if(in_array($w->status, ['requested','approved']))
                            <form method="POST" action="{{ route('agent.withdrawal.pay', $w->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-success" title="Money sent — debits the wallet and posts the payment"><i class="fa fa-paper-plane"></i> Mark paid</button>
                            </form>
                            <form method="POST" action="{{ route('agent.withdrawal.reject', $w->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning" title="Turn down the request"><i class="fa fa-times"></i></button>
                            </form>
                            @endif
                            <a href="{{ route('agent.withdrawal.edit', $w->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('agent_finance_delete') && $w->status !== 'paid')
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('agent.withdrawal.delete', $w->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $w->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
