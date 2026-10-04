@extends('backend.partials.master')
@section('title')
    {{ ___('menus.schedules') }} {{ ___('label.edit') }}
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
                                <li class="breadcrumb-item"><a href="{{ route('tour.schedule') }}"
                                        class="breadcrumb-link active">{{ ___('label.schedules') }}</a></li>
                                <li class="breadcrumb-item"><a href=""
                                        class="breadcrumb-link active">{{ ___('label.edit') }}</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">

                <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site">{{ ___('label.edit') }} {{ ___('menus.schedules') }} </h4>
                </div>
                <div class="tv-card-body">
                        <form action="{{ route('tour.scheduleUpdate', $tour_schedule->id) }}" method="post"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ ___('label.package_title') }} <span class="text-danger">*</span></label>

                                    <select name="package_id" class="form-control input-style-1 select2">
                                        <option value="">Select Package</option>

                                        @foreach ($packages as $package)
                                            <option value="{{ $package->id }}" @selected(old('package_id', $tour_schedule->package_id) == $package->id)>
                                                {{ $package->title }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('package_id')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label class=" label-style-1" for="start_date">{{ ___('label.start_date') }}</label>
                                    <span class="text-danger">*</span>
                                    <input type="date" placeholder="{{ ___('placeholder.enter_start_date') }}"
                                        class="form-control input-style-1" name="start_date"
                                      value="{{ old('start_date', \Carbon\Carbon::parse($tour_schedule->start_date)->format('Y-m-d')) }}">
                                    @error('start_date')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label class=" label-style-1" for="end_date">{{ ___('label.end_date') }}</label> <span
                                        class="text-danger">*</span>
                                    <input type="date" placeholder="{{ ___('placeholder.enter_end_date') }}"
                                        class="form-control input-style-1" name="end_date" 
                                        value="{{ old('end_date', \Carbon\Carbon::parse($tour_schedule->end_date)->format('Y-m-d')) }}">
                                    @error('end_date')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label class=" label-style-1" for="seats">{{ ___('label.seats') }}</label> <span
                                        class="text-danger">*</span>
                                    <input type="number" placeholder="{{ ___('placeholder.enter_seats') }}" min="1"
                                        class="form-control input-style-1" name="seats" value="{{ old('seats', $tour_schedule->seats) }}">
                                    @error('seats')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label class=" label-style-1" for="booked">{{ ___('label.booked') }}</label> <span
                                        class="text-danger">*</span>
                                    <input type="number" placeholder="{{ ___('placeholder.enter_booked') }}"
                                        min="0" class="form-control input-style-1" name="booked"
                                        value="{{ old('booked', $tour_schedule->booked) }}">
                                    @error('booked')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>



                                <div class="form-group col-md-6">
                                    <label class=" label-style-1" for="status">{{ ___('label.status') }}</label> <span
                                        class="text-danger">*</span>
                                    <select name="status" class="form-control input-style-1 select2">
                                        <option value="open" @if (old('status', $tour_schedule->status) != 'closed') selected @endif>
                                            {{ ___('label.open') }}</option>
                                        <option value="closed" @if (old('status', $tour_schedule->status) == 'closed') selected @endif>
                                            {{ ___('label.closed') }}</option>
                                    </select>
                                    <small class="text-muted">Full is set automatically once every seat is booked.</small>
                                    @error('status')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>

                            <div class="j-create-btns">
                                <div class="drp-btns">
                                    <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                                    <a href="{{ route('tour.schedule') }}" class="j-td-btn btn-red">
                                        <span>{{ ___('label.cancel') }}</span> </a>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
