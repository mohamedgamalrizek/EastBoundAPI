@extends('backend.partials.master')
@section('title') {{ ___('label.hotel_allocation') }} @endsection
@section('maincontent')
<x-page :title="___('label.hotel_allocation')" :breadcrumb="[___('label.hajj_umrah'), ___('label.hotel_allocation')]">

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

    {{-- Makkah and Madinah are separate stays with separate hotels, so both
         are set here rather than a single "hotel" field.

         The forms live outside the table and the inputs point at them with
         form="…": a <form> between <tr> and <td> is invalid markup and gets
         hoisted out of the table by the browser. --}}
    <x-data-table :headers="[
        ___('label.id'), ___('label.pilgrim'), ___('label.group_name'),
        ___('label.makkah_hotel'), ___('label.madinah_hotel'), ___('label.room_no'), ___('label.action')
    ]">
        @forelse($pilgrims as $p)
            @php $fid = 'hotel-alloc-' . $p->id; @endphp
            <tr>
                <td><b>{{ $p->pilgrim_no }}</b></td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->group_name ?: '—' }}</td>
                @if(hasPermission('hajj_update'))
                    <td>
                        <select form="{{ $fid }}" name="makkah_hotel" data-room-source="{{ $p->id }}"
                                class="form-control input-style-1 form-control-sm">
                            <option value="">{{ ___('label.makkah_hotel') }}</option>
                            @foreach($makkahHotels as $hotel)
                                <option value="{{ $hotel }}" @selected($p->makkah_hotel === $hotel)>{{ $hotel }}</option>
                            @endforeach
                            {{-- A hotel allocated before it left the Hotel module must still show. --}}
                            @if($p->makkah_hotel && ! $makkahHotels->contains($p->makkah_hotel))
                                <option value="{{ $p->makkah_hotel }}" selected>{{ $p->makkah_hotel }}</option>
                            @endif
                        </select>
                    </td>
                    <td>
                        <select form="{{ $fid }}" name="madinah_hotel" data-room-source="{{ $p->id }}"
                                class="form-control input-style-1 form-control-sm">
                            <option value="">{{ ___('label.madinah_hotel') }}</option>
                            @foreach($madinahHotels as $hotel)
                                <option value="{{ $hotel }}" @selected($p->madinah_hotel === $hotel)>{{ $hotel }}</option>
                            @endforeach
                            @if($p->madinah_hotel && ! $madinahHotels->contains($p->madinah_hotel))
                                <option value="{{ $p->madinah_hotel }}" selected>{{ $p->madinah_hotel }}</option>
                            @endif
                        </select>
                    </td>
                    <td>
                        {{-- Rooms in the Hotel module are types, not numbered doors, so the
                             selected hotels' types are offered as suggestions on a field the
                             office can still type an actual room number into. --}}
                        <input type="text" form="{{ $fid }}" name="room_no" list="rooms-{{ $p->id }}"
                               class="form-control input-style-1 form-control-sm tv-max-w-110"
                               value="{{ $p->room_no }}" placeholder="{{ ___('label.room_no') }}" autocomplete="off">
                        <datalist id="rooms-{{ $p->id }}"></datalist>
                    </td>
                    <td><button type="submit" form="{{ $fid }}" class="btn btn-sm btn-primary"><i class="fa fa-check"></i></button></td>
                @else
                    <td>{{ $p->makkah_hotel ?: '—' }}</td>
                    <td>{{ $p->madinah_hotel ?: '—' }}</td>
                    <td>{{ $p->room_no ?: '—' }}</td>
                    <td>—</td>
                @endif
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ ___('alert.no_data_available') }}</td></tr>
        @endforelse
    </x-data-table>

    @if(hasPermission('hajj_update'))
        @foreach($pilgrims as $p)
            <form id="hotel-alloc-{{ $p->id }}" action="{{ route('hajj.allocate', $p->id) }}" method="post" class="d-none">
                @csrf @method('PUT')
            </form>
        @endforeach
    @endif

</x-page>
@endsection

@push('scripts')
<script>
    // Room suggestions follow whichever hotels the row has selected.
    (function () {
        const roomsByHotel = @json($roomsByHotel);

        function refresh(pilgrimId) {
            const list = document.getElementById('rooms-' + pilgrimId);
            if (!list) return;

            const rooms = [];
            document.querySelectorAll('[data-room-source="' + pilgrimId + '"]').forEach(function (select) {
                (roomsByHotel[select.value] || []).forEach(function (room) {
                    const label = select.value + ' — ' + room;
                    if (!rooms.includes(label)) rooms.push(label);
                });
            });

            list.innerHTML = rooms.map(function (room) {
                return '<option value="' + room.replace(/"/g, '&quot;') + '">';
            }).join('');
        }

        document.querySelectorAll('[data-room-source]').forEach(function (select) {
            refresh(select.dataset.roomSource);
            select.addEventListener('change', function () {
                refresh(select.dataset.roomSource);
            });
        });
    })();
</script>
@endpush
