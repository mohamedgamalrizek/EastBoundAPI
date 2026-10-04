@extends('backend.partials.master')
@section('title')
    {{ ___('label.guide') }} {{ ___('label.list') }}
@endsection
@section('maincontent')
    <div class="container-fluid  dashboard-content">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <div class="page-header">
                    <div class="page-breadcrumb">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                                        class="breadcrumb-link">{{ ___('label.dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href=""
                                        class="breadcrumb-link active">{{ ___('label.guide') }}</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

                <div class="j-parcel-main j-parcel-res">
                    <div class="tv-card">
                        <div class="tv-card-head mb-3">
                            <h4 class="title-site">{{ ___('label.tour_guide') }} </h4>
                            <x-how-it-works />

                            <a href="{{ route('tour.guidesCreate') }}" class="j-td-btn"> <img
                                    src="{{ asset('backend') }}/icons/icon//plus-white.png" class="jj" alt="no image">
                                <span>{{ ___('menus.add') }}</span> </a>

                        </div>

                        <div class="tv-card-body">

                            <x-data-table :headers="['#', 'Name', 'Phone', 'Languages', 'Experience', 'Rating', 'Status', 'Action']" :card="false">

                                @forelse($tour_guides as $key => $guide)

                                     @php
                                        $status = $guide->effective_status;
                                        $cls = $status === 'active' ? 'success' : ($status === 'on_tour' ? 'info' : 'danger');
                                     @endphp
                                    <tr id="row_{{ $guide->id }}">

                                        <td><b>{{ $tour_guides->firstItem() + $key }}</b></td>

                                        <td>{{ $guide->name }}</td>

                                        <td>{{ $guide->phone }}</td>

                                        <td>{{ $guide->languages }}</td>

                                        <td>
                                            {{ $guide->experience_years }}
                                            {{ $guide->experience_years == 1 ? 'Year' : 'Years' }}
                                        </td>

                                        <td>
                                            @if ($guide->avg_rating !== null)
                                                <i class="fa fa-star text-warning"></i> {{ $guide->avg_rating }}
                                                <small class="text-muted">({{ $guide->ratings->count() }})</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge badge-{{ $cls }}">
                                                {{ $status === 'on_tour' ? ___('label.on_tour') : ucfirst($status) }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="dropdown">
                                                <div class="d-flex align-items-center flex-wrap tv-action-gap">

                                                    <a href="{{ route('tour.guidesEdit', $guide->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>

                                                    <a href="{{ route('tour.guidesDelete', $guide->id) }}"
                                                        class="btn btn-sm btn-outline-danger" onclick="tryDelete(event)"
                                                        data-remove-id="row_{{ $guide->id }}"
                                                        data-title="{{ ___('label.delete') }}"
                                                        data-text="{{ ___('alert.this_action_cannot_be_reversed') }}"
                                                        data-confirm-button-text="{{ ___('label.delete') }}"
                                                        data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>

                                                </div>
                                            </div>
                                        </td>

                                    </tr>

                                @empty
        @endforelse

                            </x-data-table>

                            {{-- pagination --}}
                            {{-- @if ($tour_guides->count())
                                <div class="mt-3">
                                    <x-paginate-show :items="$tour_guides" />
                                </div>
                            @endif --}}

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection()
