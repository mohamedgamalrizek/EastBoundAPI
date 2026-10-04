@extends('backend.partials.master')
@section('title') Campaigns @endsection
@section('maincontent')
<x-page title="Campaigns" :breadcrumb="['Campaigns']">

    @if(hasPermission('campaign_create'))
    <x-slot name="action">
        <a href="{{ route('campaign.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Campaigns overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Name','Channel','Audience','Budget','Start Date','End Date','Status','Action']">
        @foreach($items as $r)
            @php $sc = \Illuminate\Support\Str::contains(strtolower($r->status), ['cancel','reject','inactive','closed','paused','fail']) ? 'danger' : (\Illuminate\Support\Str::contains(strtolower($r->status), ['pend','process','plan','inquiry','open']) ? 'warning' : 'success'); @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->name }}</b></td>
                <td>{{ $r->channel }}</td>
                <td>{{ $r->audience }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->budget) }}</td>
                <td>{{ optional($r->start_date) ? \Illuminate\Support\Carbon::parse($r->start_date)->format('d M Y') : '' }}</td>
                <td>{{ optional($r->end_date) ? \Illuminate\Support\Carbon::parse($r->end_date)->format('d M Y') : '' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $r->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('campaign_update'))
                        <a href="{{ route('campaign.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('campaign_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('campaign.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="Delete" data-text="This action cannot be reversed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-page>
@endsection
