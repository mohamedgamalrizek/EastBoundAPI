@extends('backend.partials.master')
@section('title') Contracts @endsection
@section('maincontent')
<x-page title="Supplier Contracts" :breadcrumb="['Suppliers','Contracts']">

    @if(hasPermission('supplier_create'))
    <x-slot name="action">
        <a href="{{ route('sup.contract.create') }}" class="j-td-btn">
            <i class="fa fa-plus mr-1"></i> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    <x-data-table :headers="['Contract','Supplier','Terms','Period','Value','Status','Action']">
        @forelse($contracts as $c)
            @php
                $badge = match ($c->status) {
                    'Active'     => 'success',
                    'Draft'      => 'info',
                    'Expired'    => 'warning',
                    default      => 'danger',
                };
            @endphp
            <tr id="row_{{ $c->id }}">
                <td>
                    <b>{{ $c->contract_no }}</b>
                    <div class="text-muted tv-text-2xs">{{ $c->title }}</div>
                </td>
                <td>
                    {{ $c->supplier->name ?? '—' }}
                    <div class="text-muted tv-text-2xs">{{ $c->supplier->type ?? '' }}</div>
                </td>
                <td>
                    {{ $c->rate_type }}
                    @if($c->rate_type === 'Commission' && $c->commission_rate !== null)
                        <div class="text-muted tv-text-2xs">{{ rtrim(rtrim($c->commission_rate, '0'), '.') }}% commission</div>
                    @endif
                    @if($c->credit_days)
                        <div class="text-muted tv-text-2xs">{{ $c->credit_days }} days credit</div>
                    @endif
                </td>
                <td>
                    {{ $c->start_date?->format('d M Y') }} —
                    {{ $c->end_date?->format('d M Y') ?? 'open ended' }}
                    @if($c->isLapsed())
                        <span class="bullet-badge bullet-badge-warning">past end date</span>
                    @endif
                </td>
                <td>{{ currency_symbol() }}{{ number_format((float) $c->value) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $badge }}">{{ $c->status }}</span></td>
                <td>
                    <div class="d-flex" data-toggle="dropdown"><a class="p-2" href="javascript:void(0)"><i class="fa fa-ellipsis-v"></i></a></div>
                    <div class="dropdown-menu">
                        @if(hasPermission('supplier_update'))
                        <a href="{{ route('sup.contract.edit', $c->id) }}" class="dropdown-item"><i class="fa fa-edit"></i> {{ ___('label.edit') }}</a>
                        @endif
                        @if(hasPermission('supplier_delete'))
                        <a class="dropdown-item" href="{{ route('sup.contract.delete', $c->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $c->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i> {{ ___('label.delete') }}</a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <x-nodata-found :colspan="7" />
        @endforelse
    </x-data-table>

</x-page>
@endsection

@push@endpush
