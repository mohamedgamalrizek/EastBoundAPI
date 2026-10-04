@extends('backend.partials.master')
@section('title') {{ ___('label.sell_hotel_stay') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.sell_hotel_stay') }}" :breadcrumb="[___('menus.agent_portal'), ___('menus.new_booking'), ___('label.hotel')]">

    <div class="row">
        <div class="col-lg-12">
            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.sell_hotel_stay') }}</h4></div>
                <div class="tv-card-body">
                    {{-- No price field: the stay is quoted from the room's\n                         published rate on save, and the commission is worked\n                         out once the client has actually paid the agency. --}}
                    <form method="POST" action="{{ route('agent.hotel.store') }}">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="hotel_id">{{ ___('label.hotel') }} <span class="text-danger">*</span></label>
                                <select id="hotel_id" name="hotel_id" class="form-control input-style-1 select2">
                                    <option value="">{{ ___('label.select_a_hotel') }}</option>
                                    @foreach($hotels as $h)
                                        <option value="{{ $h->id }}" @selected(old('hotel_id') == $h->id)>
                                            {{ $h->name }}{{ $h->city ? ' — ' . $h->city : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hotel_id') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="hotel_room_id">{{ ___('label.room') }} <span class="text-danger">*</span></label>
                                <select id="hotel_room_id" name="hotel_room_id" class="form-control input-style-1 select2">
                                    <option value="">{{ ___('label.select_a_room') }}</option>
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}" data-hotel-id="{{ $r->hotel_id }}" @selected(old('hotel_room_id') == $r->id)>
                                            {{ $r->room_type }} — {{ currency_symbol() }}{{ number_format($r->rate_per_night) }} {{ ___('label.per_night') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hotel_room_id') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="client_name">{{ ___('label.client_name') }} <span class="text-danger">*</span></label>
                                <input type="text" id="client_name" name="client_name" class="form-control input-style-1"
                                       value="{{ old('client_name') }}" placeholder="{{ ___('label.who_is_staying') }}">
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
                                <label class="label-style-1" for="check_in">{{ ___('label.check_in') }} <span class="text-danger">*</span></label>
                                <input type="date" id="check_in" name="check_in" class="form-control input-style-1"
                                       value="{{ old('check_in') }}">
                                @error('check_in') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="check_out">{{ ___('label.check_out') }} <span class="text-danger">*</span></label>
                                <input type="date" id="check_out" name="check_out" class="form-control input-style-1"
                                       value="{{ old('check_out') }}">
                                @error('check_out') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <button type="submit" class="j-td-btn">{{ ___('label.raise_hotel_booking') }}</button>
                        <a href="{{ route('agent.bookings') }}" class="btn btn-outline-secondary">{{ ___('label.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-page>
@endsection

@push('scripts')
<script src="{{ asset('backend/js/custom/hotel_booking_room_filter.js') }}"></script>
@endpush
