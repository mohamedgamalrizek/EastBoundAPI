@extends('backend.partials.master')
@section('title')
Tour Packages
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
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">Tour Packages</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="j-parcel-main j-parcel-res">
                <div class="tv-card">
                    <div class="tv-card-head mb-3">
                        <h4 class="title-site">Tour Packages</h4>
                        <x-how-it-works />
                        <a href="{{ route('package.create') }}" class="j-td-btn">
                            <img src="{{ asset('backend') }}/icons/icon//plus-white.png" class="jj" alt="add"> <span>Add</span>
                        </a>
                    </div>

                    <div class="tv-card-body">
                        <x-data-table :card="false" :headers="['ID','Image','Title','Destination','Category','Price','Duration','Status','Action']">
                                    @foreach($packages as $package)
                                    <tr id="row_{{ $package->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if($package->image)
                                            <img src="{{ asset($package->image) }}" alt="" class="tv-img-48x40 tv-object-cover tv-img-radius-6">
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $package->title }}</td>
                                        <td>{{ $package->destination }}</td>
                                        <td>{{ $package->category }}</td>
                                        <td>{{ currency_symbol() }}{{ number_format($package->price) }}</td>
                                        <td>{{ $package->duration_days }}D / {{ $package->duration_nights }}N</td>
                                        <td>{!! $package->statusBadge() !!}</td>
                                        <td>
                                            <div class="d-flex align-items-center tv-action-gap">
                                                <a href="{{ route('package.edit', $package->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                <a href="{{ route('package.delete', $package->id) }}" class="btn btn-sm btn-outline-danger"
                                                    onclick="tryDelete(event)"
                                                    data-remove-id="row_{{ $package->id }}"
                                                    data-title="Delete"
                                                    data-text="This action cannot be reversed."
                                                    data-confirm-button-text="Delete"
                                                    data-cancel-button-text="Cancel"
                                                    title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </a>
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
