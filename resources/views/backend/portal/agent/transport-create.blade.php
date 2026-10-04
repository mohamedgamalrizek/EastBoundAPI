@extends('backend.partials.master')
@section('title') {{ ___('label.sell_transport') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.sell_transport') }}" :breadcrumb="[___('menus.agent_portal'), ___('menus.new_booking'), ___('label.transport')]">

    <div class="row">
        <div class="col-lg-12">
            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.sell_transport_trip') }}</h4></div>
                <div class="tv-card-body">
                    {{-- No fare field: the trip opens Pending at 0 and the desk\n                         prices it on confirmation. The commission is worked\n                         out once the client has actually paid. --}}
                    <form method="POST" action="{{ route('agent.transport.store') }}">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="type">{{ ___('label.type') }} <span class="text-danger">*</span></label>
                                <select id="type" name="type" class="form-control input-style-1 select2">
                                    <option value="">{{ ___('label.select') }}</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t }}" @selected(old('type') === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                                @error('type') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="direction">{{ ___('label.direction') }} <small class="text-muted">{{ ___('label.airport_only') }}</small></label>
                                <select id="direction" name="direction" class="form-control input-style-1 select2">
                                    <option value="">{{ ___('label.select') }}</option>
                                    @foreach($directions as $d)
                                        <option value="{{ $d }}" @selected(old('direction') === $d)>{{ $d }}</option>
                                    @endforeach
                                </select>
                                @error('direction') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="travel_date">{{ ___('label.travel_date') }} <span class="text-danger">*</span></label>
                                <input type="date" id="travel_date" name="travel_date" class="form-control input-style-1"
                                       value="{{ old('travel_date') }}">
                                @error('travel_date') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="fare">{{ ___('label.fare') }} ({{ currency_symbol() }})</label>
                                <input type="number" step="0.01" min="0" id="fare" name="fare" class="form-control input-style-1"
                                       placeholder="0.00" value="{{ old('fare') }}">
                                <small class="text-muted d-block mt-1">{{ ___('label.fare_blank_hint') }}</small>
                                @error('fare') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="route">{{ ___('label.route') }} <span class="text-danger">*</span></label>
                                <input type="text" id="route" name="route" class="form-control input-style-1"
                                       value="{{ old('route') }}" placeholder="{{ ___('label.route_arrow_example') }}">
                                @error('route') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="vehicle">{{ ___('label.vehicle') }}</label>
                                @php $currentVehicle = old('vehicle', ''); @endphp
                                <select id="vehicle" name="vehicle" class="form-control input-style-1 select2">
                                    <option value="">{{ ___('label.not_specified') }}</option>
                                    @foreach($vehicleCategories as $vc)
                                        <option value="{{ $vc }}" @selected($currentVehicle === $vc)>{{ $vc }}</option>
                                    @endforeach
                                </select>
                                @error('vehicle') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="driver_id">{{ ___('label.driver') }}</label>
                                <select id="driver_id" name="driver_id" class="form-control input-style-1 select2">
                                    <option value="">{{ ___('label.not_assigned') }}</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}" @selected(old('driver_id') == $driver->id)>{{ $driver->name }}</option>
                                    @endforeach
                                </select>
                                @error('driver_id') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
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
                        </div>

                        <button type="submit" class="j-td-btn">{{ ___('label.open_trip') }}</button>
                        <a href="{{ route('agent.bookings') }}" class="btn btn-outline-secondary">{{ ___('label.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-page>
@endsection
