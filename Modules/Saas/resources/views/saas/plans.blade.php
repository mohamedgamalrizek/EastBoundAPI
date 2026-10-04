@extends('backend.partials.master')
@section('title') Plans @endsection
@section('maincontent')
<x-page title="Plans" :breadcrumb="['Super Admin','Plans']">

    <x-slot name="action">
        <a href="{{ route('saas.plan.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add Plan</span>
        </a>
    </x-slot>

    <x-data-table :headers="['Plan','Price','Billing','Max Users','Tenants','Features','Status','Action']">
        @foreach($plans as $plan)
            <tr id="row_{{ $plan->id }}">
                <td><strong>{{ $plan->name }}</strong></td>
                <td>৳{{ number_format($plan->price) }}</td>
                <td>{{ ucfirst($plan->billing_cycle) }}</td>
                <td>{{ $plan->max_users ?? 'Unlimited' }}</td>
                <td><span class="bullet-badge bullet-badge-info">{{ $plan->tenants_count }}</span></td>
                <td>{{ collect($plan->features)->count() }} features</td>
                <td><span class="bullet-badge bullet-badge-{{ $plan->status ? 'success' : 'danger' }}">{{ $plan->status ? 'Active' : 'Inactive' }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('saas.plan.edit', $plan->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('saas.plan.delete', $plan->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $plan->id }}" data-title="Delete Plan" data-text="This cannot be undone." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
@push('scripts')<script src="{{ asset('backend/js/custom/delete_ajax.js') }}"></script>@endpush
