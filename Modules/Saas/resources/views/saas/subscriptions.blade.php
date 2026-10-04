@extends('backend.partials.master')
@section('title') Subscriptions @endsection
@section('maincontent')
<x-page title="Subscriptions" :breadcrumb="['Super Admin','Subscriptions']">

    <x-slot name="action">
        <a href="{{ route('saas.subscription.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add Subscription</span>
        </a>
    </x-slot>

    <x-data-table :headers="['Tenant','Plan','Started','Renews','Amount','Status','Action']">
        @foreach($subscriptions as $sub)
            @php $s = $sub->status; @endphp
            <tr id="row_{{ $sub->id }}">
                <td>{{ optional($sub->tenant)->name ?? '—' }}</td>
                <td>{{ optional($sub->plan)->name ?? '—' }}</td>
                <td>{{ $sub->starts_at?->format('d M Y') ?? '—' }}</td>
                <td>{{ $sub->ends_at?->format('d M Y') ?? '—' }}</td>
                <td>৳{{ number_format($sub->amount) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $s === 'active' ? 'success' : ($s === 'cancelled' || $s === 'expired' ? 'danger' : 'warning') }}">{{ ucfirst($s) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('saas.subscription.edit', $sub->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('saas.subscription.delete', $sub->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $sub->id }}" data-title="Delete Subscription" data-text="This cannot be undone." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
@push('scripts')<script src="{{ asset('backend/js/custom/delete_ajax.js') }}"></script>@endpush
