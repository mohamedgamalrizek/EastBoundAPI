@extends('backend.partials.master')
@section('title')
    {{ ___('label.schedule') }} {{ ___('label.list') }}
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
                                        class="breadcrumb-link active">{{ ___('label.schedule') }}</a></li>
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
                            <h4 class="title-site">{{ ___('label.tour_schedule') }} </h4>
                            <x-how-it-works />

                            <a href="{{ route('tour.scheduleCreate') }}" class="j-td-btn"> <img
                                    src="{{ asset('backend') }}/icons/icon//plus-white.png" class="jj" alt="no image">
                                <span>{{ ___('menus.add') }}</span> </a>

                        </div>

                        <div class="tv-card-body">

                            <x-data-table :headers="['#', 'Package', 'Departure', 'Return', 'Booked / Seats', 'Status', 'Action']" :card="false">

    @forelse($tour_schedules as $key => $schedule)

        @php
            $cls = $schedule->status_class;
        @endphp

        <tr id="row_{{ $schedule->id }}">

            <td>
                <b>{{ $tour_schedules->firstItem() + $key }}</b>
            </td>

            <td>
                <b>{{ $schedule->package_title }}</b>
            </td>

            <td>
                {{ $schedule->start_date ? \Carbon\Carbon::parse($schedule->start_date)->format('d M Y') : '-' }}
            </td>

            <td>
                {{ $schedule->end_date ? \Carbon\Carbon::parse($schedule->end_date)->format('d M Y') : '-' }}
            </td>

            <td>
                {{ $schedule->booked }} / {{ $schedule->seats }}
            </td>

            <td>
                <span class="bullet-badge bullet-badge-{{ $cls }}">
                    {{ ucfirst($schedule->effective_status) }}
                </span>
            </td>

            <td>
                <div class="dropdown">
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">

                        <a href="{{ route('tour.scheduleEdit', $schedule->id) }}"
                            class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>

                        <a href="{{ route('tour.scheduleDelete', $schedule->id) }}"
                            class="btn btn-sm btn-outline-danger"
                            onclick="tryDelete(event)"
                            data-remove-id="row_{{ $schedule->id }}"
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
                            {{-- @if ($tour_schedules->count())
                                <div class="mt-3">
                                    <x-paginate-show :items="$tour_schedules" />
                                </div>
                            @endif --}}

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection()

@push@endpush
