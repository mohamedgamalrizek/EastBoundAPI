@extends('backend.partials.master')
@section('title')
Customers
@endsection
@section('maincontent')
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">Customers</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <x-list-analytics
                title="Customers overview"
                :stats="$analytics['stats']"
                :donut="$analytics['donut']"
                :trend="$analytics['trend']" />
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="j-parcel-main j-parcel-res">
                <div class="tv-card">
                    <div class="tv-card-head mb-3">
                        <h4 class="title-site">Customers</h4>
                        <x-how-it-works />
                        <a href="{{ route('customer.create') }}" class="j-td-btn">
                            <img src="{{ asset('backend') }}/icons/icon//plus-white.png" class="jj" alt="add"> <span>Add</span>
                        </a>
                    </div>

                    <div class="tv-card-body">
                        <x-data-table :card="false" :headers="['ID','Name','Email','Phone','Tier','Status','Action']">
                                    @foreach($customers as $customer)
                                    <tr id="row_{{ $customer->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->email ?? '—' }}</td>
                                        <td>{{ $customer->phone ?? '—' }}</td>
                                        <td>{!! $customer->tierBadge() !!}</td>
                                        <td>{!! $customer->statusBadge() !!}</td>
                                        <td>
                                            <div class="input-group">
                                                <div class="input-group-prepend be-addon">
                                                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                                                        <a href="{{ route('customer.show', $customer->id) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="fa fa-eye"></i></a>
                                                        <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                        <a class="btn btn-sm btn-outline-danger" href="{{ route('customer.delete', $customer->id) }}"
                                                            onclick="tryDelete(event)"
                                                            data-remove-id="row_{{ $customer->id }}"
                                                            data-title="Delete"
                                                            data-text="This action cannot be reversed."
                                                            data-confirm-button-text="Delete"
                                                            data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                        </x-data-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push@endpush
