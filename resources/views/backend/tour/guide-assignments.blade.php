@extends('backend.partials.master')
@section('title')
    {{ ___('label.guide_assignments') }}
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
                                <li class="breadcrumb-item"><a href="{{ route('tour.guides') }}"
                                        class="breadcrumb-link active">{{ ___('label.guides') }}</a></li>
                                <li class="breadcrumb-item"><a href=""
                                        class="breadcrumb-link active">{{ ___('label.guide_assignments') }}</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">

                {{-- Assign a guide --}}
                <div class="tv-card mb-4">
                    <div class="card-header">
                        <h4 class="title-site">{{ ___('label.assign_guide') }}</h4>
                    </div>
                    <div class="tv-card-body">
                        <form action="{{ route('tour.guideAssignmentsStore') }}" method="post">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>{{ ___('label.guide') }} <span class="text-danger">*</span></label>
                                    <select name="tour_guide_id" class="form-control input-style-1 select2">
                                        @foreach ($tour_guides as $g)
                                            <option value="{{ $g->id }}" @selected(old('tour_guide_id') == $g->id)>
                                                {{ $g->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tour_guide_id')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label>{{ ___('label.tour') }} <span class="text-danger">*</span></label>
                                    <select name="package_id" class="form-control input-style-1 select2">
                                        @foreach ($packages as $p)
                                            <option value="{{ $p->id }}" @selected(old('package_id') == $p->id)>
                                                {{ $p->title }} ({{ $p->destination }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('package_id')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-2">
                                    <label>{{ ___('label.start_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control input-style-1" name="start_date"
                                        value="{{ old('start_date') }}">
                                    @error('start_date')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-2">
                                    <label>{{ ___('label.end_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control input-style-1" name="end_date"
                                        value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-2">
                                    <label>{{ ___('label.note') }}</label>
                                    <input type="text" class="form-control input-style-1" name="notes"
                                        value="{{ old('notes') }}">
                                </div>
                            </div>

                            <div class="j-create-btns">
                                <div class="drp-btns">
                                    <button type="submit" class="j-td-btn">{{ ___('label.assign_guide') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Assignment list --}}
                <div class="j-parcel-main j-parcel-res">
                    <div class="tv-card">
                        <div class="tv-card-head mb-3">
                            <h4 class="title-site">{{ ___('label.guide_assignments') }}</h4>
                            <x-how-it-works />
                        </div>

                        <div class="tv-card-body">

                            <x-data-table :headers="['#', 'Guide', 'Tour', 'Dates', 'Status', 'Action']" :card="false">

                                @forelse($assignments as $key => $a)
                                    <tr id="row_{{ $a->id }}">

                                        <td><b>{{ $assignments->firstItem() + $key }}</b></td>

                                        <td>{{ $a->guide?->name }}</td>

                                        <td>{{ $a->package?->title }}</td>

                                        <td>
                                            {{ $a->start_date->format('d M Y') }} —
                                            {{ $a->end_date->format('d M Y') }}
                                        </td>

                                        <td>
                                            <span class="badge badge-{{ $a->status_class }}">
                                                {{ ucwords(str_replace('_', ' ', $a->status)) }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center flex-wrap tv-action-gap">

                                                @if ($a->status === 'scheduled')
                                                    <form action="{{ route('tour.guideAssignmentsStatus', $a->id) }}" method="post">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="status" value="in_progress">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning"
                                                            title="{{ ___('label.start') }}"><i class="fa fa-play"></i></button>
                                                    </form>
                                                    <form action="{{ route('tour.guideAssignmentsStatus', $a->id) }}" method="post">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                            title="{{ ___('label.cancel') }}"><i class="fa fa-ban"></i></button>
                                                    </form>
                                                @endif

                                                @if ($a->status === 'in_progress')
                                                    <form action="{{ route('tour.guideAssignmentsStatus', $a->id) }}" method="post">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-sm btn-outline-success"
                                                            title="{{ ___('label.complete') }}"><i class="fa fa-check"></i></button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('tour.guideAssignmentsDelete', $a->id) }}"
                                                    class="btn btn-sm btn-outline-danger" onclick="tryDelete(event)"
                                                    data-remove-id="row_{{ $a->id }}"
                                                    data-title="{{ ___('label.delete') }}"
                                                    data-text="{{ ___('alert.this_action_cannot_be_reversed') }}"
                                                    data-confirm-button-text="{{ ___('label.delete') }}"
                                                    data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>

                                            </div>
                                        </td>

                                    </tr>

                                @empty
                                @endforelse

                            </x-data-table>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection()

@push@endpush
