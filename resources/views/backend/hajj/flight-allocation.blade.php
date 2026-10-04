@extends('backend.partials.master')
@section('title') {{ ___('label.flight_allocation') }} @endsection
@section('maincontent')
<x-page :title="___('label.flight_allocation')" :breadcrumb="[___('label.hajj_umrah'), ___('label.flight_allocation')]">

    <div class="row">
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.allocated') }}</div>
            <h3 class="mb-0 text-success">{{ $allocated }}</h3>
        </div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.pending_allocation') }}</div>
            <h3 class="mb-0 text-danger">{{ $pending }}</h3>
        </div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.hajj_pilgrims') }}</div>
            <h3 class="mb-0">{{ $pilgrims->count() }}</h3>
        </div></div></div>
    </div>

    {{-- Flights are set up once, here, and then picked from the table below.
         The seat list comes from the flight's own seat map, so nobody types a
         seat that does not exist or one that is already somebody else's. --}}
    @if(hasPermission('hajj_create'))
    <div class="row"><div class="col-12"><div class="tv-card">
        <div class="tv-card-head">
            <h4 class="title-site mb-0">{{ ___('label.add_flight') }}</h4>
        </div>
        <div class="tv-card-body">
            <form action="{{ route('hajj.flight.store') }}" method="post" class="form-row align-items-end">
                @csrf
                <div class="form-group col-md-2">
                    <label class="label-style-1">{{ ___('label.flight_no') }}</label>
                    <input type="text" name="flight_no" class="form-control input-style-1" placeholder="BG-1011" value="{{ old('flight_no') }}" required>
                    @error('flight_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-2">
                    <label class="label-style-1">{{ ___('label.airline') }}</label>
                    <input type="text" name="airline" class="form-control input-style-1" value="{{ old('airline') }}">
                </div>
                <div class="form-group col-md-2">
                    <label class="label-style-1">{{ ___('label.departure') }}</label>
                    <input type="date" name="departure_date" class="form-control input-style-1" value="{{ old('departure_date') }}">
                </div>
                <div class="form-group col-md-2">
                    <label class="label-style-1">{{ ___('label.return') }}</label>
                    <input type="date" name="return_date" class="form-control input-style-1" value="{{ old('return_date') }}">
                    @error('return_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-1">
                    <label class="label-style-1">{{ ___('label.seat_rows') }}</label>
                    <input type="number" name="seat_rows" class="form-control input-style-1" min="1" max="100" value="{{ old('seat_rows', 30) }}">
                </div>
                <div class="form-group col-md-2">
                    <label class="label-style-1">{{ ___('label.seat_letters') }}</label>
                    <input type="text" name="seat_letters" class="form-control input-style-1" placeholder="ABCDEF" value="{{ old('seat_letters', 'ABCDEF') }}">
                    @error('seat_letters') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">{{ ___('label.add') }}</button>
                </div>
            </form>

            @if($flights->count())
            <div class="table-responsive mt-3">
                <table class="table table-responsive-sm mb-0">
                    <thead class="bg"><tr>
                        <th>{{ ___('label.flight_no') }}</th>
                        <th>{{ ___('label.airline') }}</th>
                        <th>{{ ___('label.departure') }}</th>
                        <th>{{ ___('label.return') }}</th>
                        <th>{{ ___('label.seats') }}</th>
                        <th>{{ ___('label.action') }}</th>
                    </tr></thead>
                    <tbody>
                        @foreach($flights as $flight)
                        @php $booked = $flight->pilgrims()->count(); @endphp
                        <tr id="flight-row-{{ $flight->id }}">
                            <td><b>{{ $flight->flight_no }}</b></td>
                            <td>{{ $flight->airline ?: '—' }}</td>
                            <td>{{ $flight->departure_date?->format('d M Y') ?: '—' }}</td>
                            <td>{{ $flight->return_date?->format('d M Y') ?: '—' }}</td>
                            <td>{{ $booked }} / {{ $flight->seatCount() }}</td>
                            <td>
                                @if(hasPermission('hajj_delete') && $booked === 0)
                                    <a class="btn btn-sm btn-outline-danger" href="{{ route('hajj.flight.delete', $flight->id) }}"
                                       onclick="tryDelete(event)" data-title="{{ ___('label.delete') }}"
                                       data-text="{{ ___('alert.this_action_cannot_be_reversed') }}"
                                       data-confirm-button-text="{{ ___('label.delete') }}"
                                       data-cancel-button-text="{{ ___('label.cancel') }}"
                                       data-remove-id="flight-row-{{ $flight->id }}"><i class="fa fa-trash"></i></a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div></div></div>
    @endif

    {{-- Inputs point at forms rendered after the table; a <form> wrapping <td>
         elements is invalid markup and gets hoisted out by the browser. --}}
    <x-data-table :headers="[
        ___('label.id'), ___('label.pilgrim'), ___('label.group_name'),
        ___('label.flight_no'), ___('label.departure'), ___('label.return'),
        ___('label.seat_no'), ___('label.action')
    ]">
        @forelse($pilgrims as $p)
            @php $fid = 'flight-alloc-' . $p->id; @endphp
            <tr>
                <td><b>{{ $p->pilgrim_no }}</b></td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->group_name ?: '—' }}</td>
                @if(hasPermission('hajj_update') && $flights->count())
                    <td>
                        <select form="{{ $fid }}" name="flight_no"
                                class="form-control input-style-1 form-control-sm js-flight-select"
                                data-seat-target="seat-{{ $p->id }}"
                                data-departure-target="dep-{{ $p->id }}"
                                data-return-target="ret-{{ $p->id }}">
                            <option value="">{{ ___('label.select_flight') }}</option>
                            @foreach($flights as $flight)
                                <option value="{{ $flight->flight_no }}" @selected($flight->flight_no === $p->flight_no)>{{ $flight->flight_no }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="date" id="dep-{{ $p->id }}" form="{{ $fid }}" name="departure_date" class="form-control input-style-1 form-control-sm"
                               value="{{ $p->departure_date?->format('Y-m-d') }}"></td>
                    <td><input type="date" id="ret-{{ $p->id }}" form="{{ $fid }}" name="return_date" class="form-control input-style-1 form-control-sm"
                               value="{{ $p->return_date?->format('Y-m-d') }}"></td>
                    <td>
                        {{-- Rendered for the pilgrim's current flight; the script
                             below rebuilds it whenever the flight changes. --}}
                        <select id="seat-{{ $p->id }}" form="{{ $fid }}" name="seat_no"
                                class="form-control input-style-1 form-control-sm" data-current="{{ $p->seat_no }}">
                            <option value="">{{ ___('label.select_seat') }}</option>
                            @foreach(($seatMap[$p->flight_no]['seats'] ?? []) as $seat)
                                <option value="{{ $seat['seat'] }}"
                                        @selected($seat['seat'] === $p->seat_no)
                                        @disabled($seat['taken_by'] && $seat['seat'] !== $p->seat_no)>
                                    {{ $seat['seat'] }}@if($seat['taken_by'] && $seat['seat'] !== $p->seat_no) — {{ $seat['taken_by'] }}@endif
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><button type="submit" form="{{ $fid }}" class="btn btn-sm btn-primary"><i class="fa fa-check"></i></button></td>
                @else
                    <td>{{ $p->flight_no ?: '—' }}</td>
                    <td>{{ $p->departure_date?->format('d M Y') ?: '—' }}</td>
                    <td>{{ $p->return_date?->format('d M Y') ?: '—' }}</td>
                    <td>{{ $p->seat_no ?: '—' }}</td>
                    <td>@if(hasPermission('hajj_update'))<span class="text-muted">{{ ___('label.add_a_flight_first') }}</span>@else—@endif</td>
                @endif
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">{{ ___('alert.no_data_available') }}</td></tr>
        @endforelse
    </x-data-table>

    @if(hasPermission('hajj_update'))
        @foreach($pilgrims as $p)
            <form id="flight-alloc-{{ $p->id }}" action="{{ route('hajj.allocate', $p->id) }}" method="post" class="d-none">
                @csrf @method('PUT')
            </form>
        @endforeach
    @endif

</x-page>
@endsection

@push('scripts')
<script>
    // Seat lists live in the page rather than behind an endpoint: the table
    // already knows every flight, and swapping a dropdown should not wait on
    // the network. Seats belonging to someone else are shown but disabled, so
    // the office can see who has 12A instead of wondering where it went.
    (function () {
        const seatMap = @json($seatMap);

        function fillSeats(select) {
            const seatSelect = document.getElementById(select.dataset.seatTarget);
            if (!seatSelect) return;

            const flight  = seatMap[select.value];
            const current = seatSelect.dataset.current || '';

            seatSelect.innerHTML = '';

            const blank = new Option(@json(___('label.select_seat')), '');
            seatSelect.add(blank);

            if (!flight) return;

            flight.seats.forEach(function (row) {
                const mine   = row.seat === current;
                const option = new Option(
                    row.taken_by && !mine ? row.seat + ' — ' + row.taken_by : row.seat,
                    row.seat
                );
                option.disabled = Boolean(row.taken_by) && !mine;
                option.selected = mine;
                seatSelect.add(option);
            });

            // A flight carries its own dates; filling them saves retyping the
            // same two dates on every pilgrim. Both stay editable.
            const departure = document.getElementById(select.dataset.departureTarget);
            const back      = document.getElementById(select.dataset.returnTarget);

            if (departure && !departure.value && flight.departure_date) departure.value = flight.departure_date;
            if (back && !back.value && flight.return_date) back.value = flight.return_date;
        }

        document.querySelectorAll('.js-flight-select').forEach(function (select) {
            select.addEventListener('change', function () { fillSeats(select); });
        });
    })();
</script>
@endpush
