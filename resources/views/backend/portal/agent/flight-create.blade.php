@extends('backend.partials.master')
@section('title') {{ ___('label.sell_flight') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.sell_flight') }}" :breadcrumb="[___('menus.agent_portal'), ___('menus.new_booking'), ___('label.flight')]">

    <div class="row">
        <div class="col-lg-12">
            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.sell_flight_trip') }}</h4></div>
                <div class="tv-card-body">
                    {{-- No pnr, ticket number or fare field: the request opens\\n                         Pending and the desk prices it and issues the ticket.\\n                         The commission is worked out once it is ticketed. --}}
                    <form method="POST" action="{{ route('agent.flight.store') }}">
                        @csrf
                        <div class="row">
                            {{-- From/To are the same searchable select2 lists the admin
                                 flight form uses, so the agent picks a route the way the
                                 desk does. The submitted airport codes are joined into the
                                 stored route server-side (see StoreAgentFlightRequest). --}}
                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="from">{{ ___('label.from') }} <span class="text-danger">*</span></label>
                                <select id="from" name="from" class="form-control input-style-1 select2" required>
                                    <option value="">{{ ___('label.select') }}</option>
                                    @foreach($cities as $c)
                                        <option value="{{ $c->code ?: $c->city }}" @selected(old('from') === ($c->code ?: $c->city))>{{ $c->city }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                                    @endforeach
                                </select>
                                @error('from') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="to">{{ ___('label.to') }} <span class="text-danger">*</span></label>
                                <select id="to" name="to" class="form-control input-style-1 select2" required>
                                    <option value="">{{ ___('label.select') }}</option>
                                    @foreach($cities as $c)
                                        <option value="{{ $c->code ?: $c->city }}" @selected(old('to') === ($c->code ?: $c->city))>{{ $c->city }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                                    @endforeach
                                </select>
                                @error('to') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                                @error('route') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="flight_date">{{ ___('label.travel_date') }} <span class="text-danger">*</span></label>
                                <input type="date" id="flight_date" name="flight_date" class="form-control input-style-1"
                                       value="{{ old('flight_date') }}">
                                @error('flight_date') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="passenger_name">{{ ___('label.passenger_name') }} <span class="text-danger">*</span></label>
                                <input type="text" id="passenger_name" name="passenger_name" class="form-control input-style-1"
                                       value="{{ old('passenger_name') }}" placeholder="{{ ___('label.name_on_ticket_placeholder') }}">
                                @error('passenger_name') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="airline">{{ ___('label.preferred_airline') }}</label>
                                <input type="text" id="airline" name="airline" class="form-control input-style-1"
                                       value="{{ old('airline') }}" placeholder="{{ ___('label.airline_optional_hint') }}">
                                @error('airline') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="client_name">{{ ___('label.client_name') }} <span class="text-danger">*</span></label>
                                <input type="text" id="client_name" name="client_name" class="form-control input-style-1"
                                       value="{{ old('client_name') }}" placeholder="{{ ___('label.who_is_buying_ticket') }}">
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

                        <button type="submit" class="j-td-btn">{{ ___('label.file_request') }}</button>
                        <a href="{{ route('agent.bookings') }}" class="btn btn-outline-secondary">{{ ___('label.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-page>
@endsection
