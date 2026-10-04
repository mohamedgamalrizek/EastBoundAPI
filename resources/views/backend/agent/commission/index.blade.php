@extends('backend.partials.master')
@section('title') {{ ___('label.agent') }} {{ ___('label.commission') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.commission') }}" :breadcrumb="['Agent','Commission']">

    @if(hasPermission('agent_finance_create'))
    <x-slot name="action">
        <a href="{{ route('agent.commission.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Reference','Agent','Customer','Booking Ref','Amount','Rate (%)','Source','Status','Action']">
        @foreach($items as $c)
            @php $badge = $c->status === 'paid' ? 'success' : 'warning'; @endphp
            <tr id="row_{{ $c->id }}">
                <td><b>{{ $c->reference }}</b></td>
                <td>{{ $c->agent->name ?? '—' }}</td>
                <td>{{ $c->customer_name }}</td>
                <td>{{ $c->booking_ref }}</td>
                <td>{{ currency_symbol() }}{{ number_format($c->amount, 2) }}</td>
                <td>{{ number_format($c->rate, 2) }}</td>
                <td>
                    @if($c->is_auto)
                        <span class="bullet-badge bullet-badge-info" title="Created from the paid booking at the agent's rate">Auto</span>
                    @else
                        <span class="bullet-badge bullet-badge-warning">Manual</span>
                    @endif
                </td>
                <td><span class="bullet-badge bullet-badge-{{ $badge }}">{{ ucfirst($c->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        {{-- Approving is what actually pays the agent: it credits
                             their wallet, from which they can request a payout. --}}
                        @if(hasPermission('agent_finance_update'))
                            @if($c->status === 'paid')
                            <form method="POST" action="{{ route('agent.commission.unapprove', $c->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Take back out of the wallet">
                                    <i class="fa fa-undo"></i> Unapprove
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('agent.commission.approve', $c->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Credit to the agent's wallet">
                                    <i class="fa fa-check"></i> Approve
                                </button>
                            </form>
                            @endif
                        @endif
                        @if(hasPermission('agent_finance_update'))
                        <a href="{{ route('agent.commission.edit', $c->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('agent_finance_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('agent.commission.delete', $c->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $c->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
