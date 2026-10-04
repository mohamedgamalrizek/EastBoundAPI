@extends('backend.partials.master')
@section('title') Testimonials @endsection
@section('maincontent')
<x-page title="Testimonials" :breadcrumb="['CMS','Testimonials']">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.testimonial.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Name','Role','Rating','Status','Action']">
        @foreach($items as $t)
            @php $c = $t->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $t->id }}">
                <td><b>{{ $t->name }}</b></td>
                <td>{{ $t->role }}</td>
                <td><span class="text-warning"><i class="fa fa-star"></i> {{ $t->rating }}</span></td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($t->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.testimonial.edit', $t->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.testimonial.delete', $t->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $t->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
