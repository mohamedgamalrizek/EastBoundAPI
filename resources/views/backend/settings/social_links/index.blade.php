@extends('backend.partials.master')
@section('title', 'Social links')
@section('maincontent')
<x-page title="Social links" :breadcrumb="['Settings', 'Social links']">
    @if(hasPermission('general_settings_update'))
    <x-slot name="action"><a href="{{ route('settings.social-links.create') }}" class="j-td-btn"><img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add</span></a></x-slot>
    @endif
    <x-data-table :headers="['Name', 'Icon', 'URL', 'Order', 'Status', 'Action']">
        @forelse($items as $item)
        <tr id="row_{{ $item->id }}">
            <td><i class="fa-brands {{ $item->icon }} me-2"></i>{{ $item->name }}</td>
            <td><code>{{ $item->icon }}</code></td>
            <td class="text-break">{{ $item->url }}</td>
            <td>{{ $item->sort_order }}</td>
            <td><span class="bullet-badge bullet-badge-{{ $item->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($item->status) }}</span></td>
            <td><div class="d-flex align-items-center flex-wrap tv-action-gap">
                @if(hasPermission('general_settings_update'))
                <a href="{{ route('settings.social-links.edit', $item) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a>
                <a class="btn btn-sm btn-outline-danger" href="{{ route('settings.social-links.destroy', $item) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $item->id }}" data-title="Delete" data-text="This action cannot be reversed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa-solid fa-trash-can"></i></a>
                @endif
            </div></td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted">No social links configured yet.</td></tr>
        @endforelse
    </x-data-table>
</x-page>
@endsection
@push@endpush
