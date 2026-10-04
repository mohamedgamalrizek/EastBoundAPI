@extends('backend.partials.master')
@section('title') Domains @endsection
@section('maincontent')
<x-page title="Domains" :breadcrumb="['Super Admin','Domains']">

    <x-slot name="action">
        <a href="{{ route('saas.domain.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add Domain</span>
        </a>
    </x-slot>

    <x-data-table :headers="['Domain','Tenant','Created','Action']">
        @foreach($domains as $domain)
            <tr id="row_{{ $domain->id }}">
                <td><b>{{ $domain->domain }}</b></td>
                <td>{{ optional($domain->tenant)->name ?? '—' }}</td>
                <td>{{ $domain->created_at?->format('d M Y') }}</td>
                <td>
                    <a class="btn btn-sm btn-outline-danger" href="{{ route('saas.domain.delete', $domain->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $domain->id }}" data-title="Remove Domain" data-text="This removes the domain mapping." data-confirm-button-text="Remove" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
@push('scripts')<script src="{{ asset('backend/js/custom/delete_ajax.js') }}"></script>@endpush
