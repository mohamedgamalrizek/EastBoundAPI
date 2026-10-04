@extends('backend.partials.master')
@section('title') {{ ___('menus.new_booking') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.new_booking') }}" :breadcrumb="[___('menus.agent_portal'), ___('permissions.bookings'), ___('label.new')]">

    <div class="row">
        <div class="col-lg-12">
            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.sell_a_tour') }}</h4></div>
                <div class="tv-card-body">
                    {{-- No price field: the fare is quoted from the catalogue on
                         save, and the commission is worked out once the client
                         has actually paid the agency. --}}
                    <form method="POST" action="{{ route('agent.booking.store') }}">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label class="label-style-1" for="package_id">{{ ___('label.tour') }} <span class="text-danger">*</span></label>
                                <select id="package_id" name="package_id" class="form-control input-style-1 select2">
                                    <option value="">{{ ___('label.select_a_tour') }}</option>
                                    @foreach($packages as $p)
                                        <option value="{{ $p->id }}" @selected(old('package_id') == $p->id)>
                                            {{ $p->title }} ({{ $p->destination }}) — {{ currency_symbol() }}{{ number_format($p->price) }} {{ ___('label.per_traveller') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('package_id') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="client_name">{{ ___('label.client_name') }} <span class="text-danger">*</span></label>
                                <input type="text" id="client_name" name="client_name" class="form-control input-style-1"
                                       value="{{ old('client_name') }}" placeholder="{{ ___('label.who_is_travelling') }}">
                                @error('client_name') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="client_phone">{{ ___('label.client_phone') }}</label>
                                <input type="text" id="client_phone" name="client_phone" class="form-control input-style-1"
                                       value="{{ old('client_phone') }}" placeholder="{{ ___('label.client_phone_example') }}">
                                @error('client_phone') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="client_email">{{ ___('label.client_email') }}</label>
                                <input type="email" id="client_email" name="client_email" class="form-control input-style-1"
                                       value="{{ old('client_email') }}" placeholder="{{ ___('label.client_email_hint') }}">
                                @error('client_email') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="travel_date">{{ ___('label.travel_date') }} <span class="text-danger">*</span></label>
                                <input type="date" id="travel_date" name="travel_date" class="form-control input-style-1"
                                       value="{{ old('travel_date') }}">
                                @error('travel_date') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="travelers">{{ ___('menus.travelers') }} <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="50" id="travelers" name="travelers" class="form-control input-style-1"
                                       value="{{ old('travelers', 1) }}">
                                @error('travelers') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <button type="submit" class="j-td-btn">{{ ___('label.raise_booking') }}</button>
                        <a href="{{ route('agent.bookings') }}" class="btn btn-outline-secondary">{{ ___('label.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-page>
@endsection
