@extends('backend.partials.master')
@section('title') Tenants @endsection
@section('maincontent')
<x-page title="Tenants" :breadcrumb="['Super Admin','Tenants']">

    <x-slot name="action">
        <a href="{{ route('saas.tenant.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add Tenant</span>
        </a>
    </x-slot>

    <x-data-table :headers="['Tenant','Email','Domain','Plan','Status','Database','Action']">
        @foreach($tenants as $tenant)
            @php $s = $tenant->status; $hasDb = isset($provisioned[$tenant->id]); @endphp
            <tr id="row_{{ $tenant->id }}">
                <td>{{ $tenant->name ?? '—' }}</td>
                <td>{{ $tenant->email ?? '—' }}</td>
                <td>{{ optional($tenant->domains->first())->domain ?? '—' }}</td>
                <td>{{ optional($tenant->plan)->name ?? '—' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $s === 'active' ? 'success' : ($s === 'suspended' ? 'danger' : 'warning') }}">{{ ucfirst($s) }}</span></td>
                <td>
                    @if($hasDb)
                        <span class="bullet-badge bullet-badge-success"><i class="fa fa-database"></i> Provisioned</span>
                    @else
                        <span class="bullet-badge bullet-badge-secondary">Not provisioned</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('saas.tenant.edit', $tenant->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                        @unless($hasDb)
                        <form action="{{ route('saas.tenant.provision', $tenant->id) }}" method="POST" class="d-inline">@csrf @method('PUT')
                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Provision DB"><i class="fa fa-database"></i></button>
                        </form>
                        @endunless
                        <form action="{{ route('saas.tenant.toggle', $tenant->id) }}" method="POST" class="d-inline">@csrf @method('PUT')
                            <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ $s === 'active' ? 'Suspend' : 'Activate' }}"><i class="fa fa-power-off"></i></button>
                        </form>
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('saas.tenant.delete', $tenant->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $tenant->id }}" data-title="Delete Tenant" data-text="This removes the tenant, its domains, subscriptions & database." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
@push('scripts')<script src="{{ asset('backend/js/custom/delete_ajax.js') }}"></script>@endpush
