@extends('frontend.layouts.master')
@section('title', $config['label'] . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', $config['intro'])
@section('page', 'booking')

@php
    $all = \App\Http\Controllers\FrontendController::bookingTypes();
    // Prefill from the query string so "Book" links on the visa / flight /
    // hotel / transport pages carry the selection into this form.
    $pre = fn ($key, $default = '') => old($key, request($key, $default));
@endphp

@section('content')
@include('frontend.components.page-hero', [
    'title' => $config['label'],
    'subtitle' => $config['intro'],
    'crumbs' => [___('frontend.book') => null, $config['label'] => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-4">
            {{-- Sidebar: other services --}}
            <div class="col-lg-4">
                <div class="card p-3 mb-4 booking-service-list">
                    <div class="px-2 pt-2 mb-3 fw-700 text-muted-2 text-14 text-uppercase tracking-wide">{{ ___('frontend.book_service') }}</div>
                    @foreach($all as $key => $cfg)
                    <a href="{{ route('front.book', $key) }}" class="mega-link {{ $key === $type ? 'active-book' : '' }}">
                        <i class="fa-solid {{ $cfg['icon'] }}"></i> {{ $cfg['label'] }}
                    </a>
                    @endforeach
                </div>
                <div class="card p-4">
                    <h5 class="mb-3"><i class="fa-solid fa-circle-check me-2 text-success-tv"></i>{{ ___('frontend.why_book_with_us') }}</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0 text-muted-2 text-14">
                        @foreach($whyBook as $reason)
                        <li><i class="fa-solid fa-check me-2 text-success-tv"></i>{{ $reason->title }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Form --}}
            <div class="col-lg-8">
                <div class="enquiry-card">
                    <div class="d-flex gap-3 mb-4">
                        <div class="sc-icon text-32 m-0"><i class="fa-solid {{ $config['icon'] }}"></i></div>
                        <div><h3 class="mb-0">{{ $config['label'] }}</h3><p class="text-muted-2 mb-0 text-14">{{ $config['intro'] }}</p></div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}</div>
                    @endif

                    <form action="{{ route('front.book.request', $type) }}" method="post"
                        @if($type === 'flight') data-flight-fare-form @endif
                        @if($type === 'visa') data-visa-fee-form @endif>
                        @csrf
                        @if($type === 'flight')
                            <script type="application/json" data-flight-fares>@json($flightFares)</script>
                        @endif
                        <div class="row g-3">

                            {{-- ---------- Type-specific fields ---------- --}}
                            @foreach($config['fields'] as $group)
                                @switch($group)
                                    @case('trip')
                                        @if($type === 'flight')
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.from') }}</label>
                                            <select class="form-select select2" name="from">
                                                <option value="">{{ ___('frontend.select') }}</option>
                                                @foreach($flightCities as $c)
                                                    <option value="{{ $c->city }}" @selected($pre('from') === $c->city)>{{ $c->city }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.to_destination') }}</label>
                                            <select class="form-select select2" name="to">
                                                <option value="">{{ ___('frontend.select') }}</option>
                                                @foreach($flightCities as $c)
                                                    <option value="{{ $c->city }}" @selected($pre('to') === $c->city)>{{ $c->city }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted-2" data-flight-fare-hint hidden data-currency="{{ currency_symbol() }}" data-no-fare-text="{{ ___('frontend.no_published_fare') }}" data-fare-estimate-format="{{ ___('frontend.fare_estimate_format') }}"></small>
                                        </div>
                                        @else
                                        <div class="col-md-6"><label class="form-label">{{ ___('frontend.from') }}</label><input class="form-control" name="from" value="{{ $pre('from') }}" placeholder="{{ ___('frontend.dhaka_example') }}"></div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.to_destination') }}</label>
                                            <input class="form-control" name="to" list="book-destinations" value="{{ $pre('to') }}" placeholder="{{ ___('frontend.dubai_maldives_example') }}">
                                            <datalist id="book-destinations">
                                                @foreach($packages->pluck('destination')->unique() as $d)<option value="{{ $d }}">@endforeach
                                            </datalist>
                                        </div>
                                        @endif
                                        @if($type === 'tour' && $packages->isNotEmpty())
                                        <div class="col-12">
                                            <label class="form-label">{{ ___('frontend.package_optional') }}</label>
                                            <select class="form-select" name="package">
                                                <option value="">{{ ___('frontend.not_sure_advise') }}</option>
                                                @foreach($packages as $p)
                                                    <option value="{{ $p->title }}" @selected($pre('package') === $p->title)>{{ $p->title }} — {{ $p->destination }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                        <div class="col-md-4">
                                            <label class="form-label">{{ ___('frontend.departure_date') }}</label>
                                            <div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control @error('departure_date') is-invalid @enderror" name="departure_date" value="{{ $pre('departure_date', $pre('depart')) }}" placeholder="{{ ___('frontend.select_date') }}"></div>
                                            @error('departure_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.return_date') }}</label><div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control" name="return_date" value="{{ $pre('return_date') }}" placeholder="{{ ___('frontend.select_date') }}"></div></div>
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.travelers') }}</label><input type="number" min="1" max="50" value="{{ $pre('travelers', 2) }}" class="form-control" name="travelers"></div>
                                        @break

                                    @case('stay')
                                        <div class="col-md-6"><label class="form-label">{{ ___('frontend.destination_city') }}</label><input class="form-control" name="city" value="{{ $pre('city') }}" placeholder="Cox's Bazar"></div>
                                        <div class="col-md-6"><label class="form-label">{{ ___('frontend.hotel_preference') }}</label><input class="form-control" name="hotel" value="{{ $pre('hotel') }}" placeholder="{{ ___('frontend.hotel_preference_placeholder') }}"></div>
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.check_in') }}</label><div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control" name="checkin" value="{{ $pre('checkin') }}" placeholder="{{ ___('frontend.select_date') }}"></div></div>
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.check_out') }}</label><div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control" name="checkout" value="{{ $pre('checkout') }}" placeholder="{{ ___('frontend.select_date') }}"></div></div>
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.guests_rooms') }}</label><input class="form-control" name="guests" value="{{ $pre('guests') }}" placeholder="2 guests, 1 room"></div>
                                        @break

                                    @case('visa')
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.destination_country') }}</label>
                                            <select class="form-select select2 @error('country') is-invalid @enderror" name="country">
                                                <option value="">{{ ___('frontend.select') }}</option>
                                                @foreach($visaCountries as $c)
                                                    <option value="{{ $c }}" @selected($pre('country') === $c)>{{ $c }}</option>
                                                @endforeach
                                            </select>
                                            @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.visa_type') }}</label>
                                            <select class="form-select" name="visa_type">
                                                {{-- Same list the visa catalogue is validated against. --}}
                                                @foreach(\App\Repositories\VisaService\VisaServiceRepository::TYPES as $vt)
                                                    <option value="{{ $vt }}" @selected($pre('visa_type') === $vt)>{{ $vt }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.nationality') }}</label>
                                            <select class="form-select select2" name="nationality">
                                                @foreach($nationalities as $n)
                                                    <option value="{{ $n }}" @selected($pre('nationality', 'Bangladeshi') === $n)>{{ $n }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6"><label class="form-label">{{ ___('frontend.intended_travel_date') }}</label><div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control" name="travel_date" value="{{ $pre('travel_date', $pre('date')) }}" placeholder="{{ ___('frontend.select_date') }}"></div></div>
                                        <script type="application/json" data-visa-fees>@json($visaFees)</script>
                                        <div class="col-12">
                                            <small class="text-muted-2" data-visa-fee-hint hidden data-currency="{{ currency_symbol() }}" data-no-fee-text="{{ ___('frontend.no_published_fee') }}" data-fee-estimate-format="{{ ___('frontend.fee_estimate_format') }}"></small>
                                        </div>
                                        @break

                                    @case('pilgrim')
                                        {{-- The package and the passport are what the Hajj
                                             module actually registers a pilgrim against, so
                                             they are asked for here rather than guessed from
                                             an "Economy / Standard / Premium" preference. --}}
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.select_package') }} *</label>
                                            <select class="form-select @error('hajj_package_id') is-invalid @enderror" name="hajj_package_id" required>
                                                <option value="">{{ ___('frontend.select_package') }}</option>
                                                @foreach($hajjPackages as $hp)
                                                    <option value="{{ $hp->id }}" @selected(old('hajj_package_id') == $hp->id) @disabled((int) $hp->seats <= 0)>
                                                        {{ $hp->title }} — {{ currency_symbol() }}{{ number_format((float) $hp->price) }}
                                                        @if((int) $hp->seats <= 0) ({{ ___('frontend.sold_out') }}) @else ({{ $hp->seats }} {{ ___('frontend.seats_left') }}) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('hajj_package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.passport_no') }} *</label>
                                            <input class="form-control @error('passport_no') is-invalid @enderror" name="passport_no" value="{{ old('passport_no') }}" placeholder="{{ ___('frontend.passport_example') }}" required>
                                            @error('passport_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        {{-- Preferences: no column on the pilgrim record, so they
                                             ride to the consultant as a CRM lead. --}}
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.number_of_pilgrims') }}</label><input type="number" min="1" max="100" value="{{ old('pilgrims', 1) }}" class="form-control" name="pilgrims"></div>
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.preferred_month') }}</label><input class="form-control" name="preferred_month" value="{{ old('preferred_month') }}" placeholder="{{ ___('frontend.preferred_month_placeholder') }}"></div>
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.room_sharing') }}</label><select class="form-select" name="room_sharing"><option>{{ ___('frontend.quad') }}</option><option>{{ ___('frontend.triple') }}</option><option>{{ ___('frontend.double') }}</option></select></div>
                                        @break

                                    @case('transfer')
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.pickup_location') }} *</label>
                                            <input class="form-control @error('pickup') is-invalid @enderror" name="pickup" value="{{ $pre('pickup') }}" placeholder="{{ ___('frontend.pickup_location') }}">
                                            @error('pickup')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.dropoff_location') }} *</label>
                                            <input class="form-control @error('dropoff') is-invalid @enderror" name="dropoff" value="{{ $pre('dropoff') }}" placeholder="{{ ___('frontend.destination') }}">
                                            @error('dropoff')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ ___('frontend.date') }} *</label>
                                            <div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control @error('date') is-invalid @enderror" name="date" value="{{ $pre('date') }}" placeholder="{{ ___('frontend.select_date') }}"></div>
                                            @error('date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.time') }}</label><input type="time" class="form-control" name="time" value="{{ $pre('time') }}"></div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ ___('frontend.vehicle') }}</label>
                                            <select class="form-select select2" name="vehicle">
                                                @foreach($vehicleCategories as $vc)
                                                    <option value="{{ $vc }}" @selected($pre('vehicle') === $vc)>{{ $vc }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @break

                                    @case('rental')
                                        <div class="col-md-6">
                                            <label class="form-label">{{ ___('frontend.vehicle_type') }}</label>
                                            <select class="form-select select2" name="vehicle">
                                                @foreach($vehicleCategories as $vc)
                                                    <option value="{{ $vc }}" @selected($pre('vehicle') === $vc)>{{ $vc }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6"><label class="form-label">{{ ___('frontend.with_driver') }}</label><select class="form-select" name="with_driver"><option>{{ ___('frontend.yes_with_driver') }}</option><option>{{ ___('frontend.no_self_drive') }}</option></select></div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ ___('frontend.pickup_date') }} *</label>
                                            <div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control @error('pickup_date') is-invalid @enderror" name="pickup_date" value="{{ $pre('pickup_date') }}" placeholder="{{ ___('frontend.select_date') }}"></div>
                                            @error('pickup_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4"><label class="form-label">{{ ___('frontend.return_date') }}</label><div class="input-icon"><i class="fa-solid fa-calendar-days"></i><input type="date" class="form-control" name="return_date" value="{{ $pre('return_date') }}" placeholder="{{ ___('frontend.select_date') }}"></div></div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ ___('frontend.pickup_city') }} *</label>
                                            <input class="form-control @error('city') is-invalid @enderror" name="city" value="{{ $pre('city') }}" placeholder="Dhaka">
                                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        @break
                                @endswitch
                            @endforeach

                            {{-- ---------- Contact details ---------- --}}
                            <div class="col-12"><hr class="divider my-2"></div>
                            <div class="col-md-4">
                                <label class="form-label">{{ ___('frontend.full_name') }} *</label>
                                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="{{ ___('frontend.your_name_placeholder') }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ ___('frontend.phone') }} *</label>
                                <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="{{ ___('frontend.phone_placeholder') }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ ___('frontend.email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="{{ ___('frontend.email_example') }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ ___('frontend.additional_details') }}</label>
                                <textarea class="form-control" name="details" rows="3" placeholder="{{ ___('frontend.additional_details_placeholder') }}">{{ old('details') }}</textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-brand btn-lg"><i class="fa-solid fa-paper-plane"></i> {{ ___('frontend.submit') }} {{ $config['label'] }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

