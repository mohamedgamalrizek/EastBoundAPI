@extends('backend.partials.master')
@section('title') Travel Insurance @endsection
@section('maincontent')
<x-page title="Travel Insurance" :breadcrumb="['Travel Insurance']">

    @if(hasPermission('insurance_create'))
    <x-slot name="action">
        <a href="{{ route('insurance.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Travel Insurance overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Provider','Plan Name','Type','Coverage','Premium','Customer','Service Fee','Status','Action']">
        @foreach($items as $r)
            @php $sc = \Illuminate\Support\Str::contains(strtolower($r->status), ['cancel','reject','inactive','closed','paused','fail']) ? 'danger' : (\Illuminate\Support\Str::contains(strtolower($r->status), ['pend','process','plan','inquiry','open']) ? 'warning' : 'success'); @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->provider }}</b></td>
                <td>{{ $r->plan_name }}</td>
                <td>{{ $r->type }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->coverage) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->premium) }}</td>
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
                        @if(hasPermission('insurance_update'))
                        <a href="{{ route('insurance.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('insurance_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('insurance.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="Delete" data-text="This action cannot be reversed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-page>
@endsection

@push@endpush
