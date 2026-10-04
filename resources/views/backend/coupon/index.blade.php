@extends('backend.partials.master')
@section('title') Coupons @endsection
@section('maincontent')
<x-page title="Coupons" :breadcrumb="['Coupons']">

    @if(hasPermission('coupon_create'))
    <x-slot name="action">
        <a href="{{ route('coupon.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Coupons overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Code','Type','Value','Min Spend','Used','Expires At','Status','Action']">
        @foreach($items as $r)
            @php $sc = \Illuminate\Support\Str::contains(strtolower($r->status), ['cancel','reject','inactive','closed','paused','fail']) ? 'danger' : (\Illuminate\Support\Str::contains(strtolower($r->status), ['pend','process','plan','inquiry','open']) ? 'warning' : 'success'); @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->code }}</b></td>
                <td>{{ $r->type }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->value) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->min_spend) }}</td>
                <td>
                    {{ number_format($r->used_count) }}{{ $r->usage_limit ? ' / ' . number_format($r->usage_limit) : '' }}
                    @if($r->isExhausted())
                        <br><small class="text-danger">Limit reached</small>
                    @elseif($r->isExpired())
                        <br><small class="text-danger">Expired</small>
                    @endif
                </td>
                <td>{{ optional($r->expires_at) ? \Illuminate\Support\Carbon::parse($r->expires_at)->format('d M Y') : '' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $r->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('coupon_update'))
                        <a href="{{ route('coupon.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('coupon_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('coupon.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="Delete" data-text="This action cannot be reversed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-page>
@endsection

@push@endpush
