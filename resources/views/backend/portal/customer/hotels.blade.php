@extends('backend.partials.master')
@section('title') {{ ___('label.hotel_bookings') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.hotel_bookings') }}" :breadcrumb="[___('permissions.customer_portal'), ___('label.hotel_bookings')]">

    {{-- Book a stay: same rows the mobile app writes; the desk confirms and
         the billing observer raises the invoice from there. --}}
    <div class="tv-card mb-3"><div class="tv-card-body">
        <h6 class="mb-3">{{ ___('label.book_a_stay') }}</h6>
        <form method="POST" action="{{ route('cust.hotels.book') }}">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label class="label-style-1" for="hotel_id">{{ ___('label.hotel') }}</label>
                    <select id="hotel_id" name="hotel_id" class="form-control input-style-1" required>
                        <option value="">{{ ___('label.select') }}</option>
                        @foreach($hotels as $h)
                            <option value="{{ $h->id }}" @selected(old('hotel_id') == $h->id)>{{ $h->name }} — {{ $h->city }}</option>
                        @endforeach
                    </select>
                    @error('hotel_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-3">
                    <label class="label-style-1" for="hotel_room_id">{{ ___('label.room') }}</label>
                    <select id="hotel_room_id" name="hotel_room_id" class="form-control input-style-1" required>
                        <option value="">{{ ___('label.select') }}</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" data-hotel-id="{{ $room->hotel_id }}" @selected(old('hotel_room_id') == $room->id)>
                                {{ $room->room_type }} ({{ currency_symbol() }}{{ number_format($room->rate_per_night) }}/night)
                            </option>
                        @endforeach
                    </select>
                    @error('hotel_room_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-2">
                    <label class="label-style-1" for="check_in">{{ ___('label.check_in') }}</label>
                    <input type="date" id="check_in" name="check_in" class="form-control input-style-1"
                           min="{{ now()->toDateString() }}" value="{{ old('check_in') }}" required>
                    @error('check_in') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-2">
                    <label class="label-style-1" for="check_out">{{ ___('label.check_out') }}</label>
                    <input type="date" id="check_out" name="check_out" class="form-control input-style-1"
                           min="{{ now()->addDay()->toDateString() }}" value="{{ old('check_out') }}" required>
                    @error('check_out') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="j-td-btn w-100">{{ ___('label.book') }}</button>
                </div>
            </div>
        </form>
    </div></div>

    <x-data-table :headers="[___('label.booking_no'), ___('label.hotel'), ___('label.room'), ___('label.check_in'), ___('label.check_out'), ___('label.nights'), ___('label.amount'), ___('label.status'), ___('label.action')]">
        @forelse($bookings as $b)
            @php
                $c = match($b->status) {
                    'Paid'      => 'success',
                    'Confirmed' => 'info',
                    'Cancelled' => 'danger',
                    default     => 'warning',
                };
                $payable = ! in_array($b->status, ['Paid', 'Cancelled'], true) && (float) $b->amount > 0;
            @endphp
            <tr>
                <td><b>{{ $b->booking_no }}</b></td>
                <td>{{ $b->hotel?->name ?? '—' }}</td>
                <td>{{ $b->hotelRoom?->room_type ?? '—' }}</td>
                <td>{{ $b->check_in?->format('d M Y') }}</td>
                <td>{{ $b->check_out?->format('d M Y') }}</td>
                <td>{{ $b->nights }}</td>
                <td>{{ currency_symbol() }}{{ number_format($b->amount, 2) }}</td>
                <td>
                    <span class="bullet-badge bullet-badge-{{ $c }}">{{ $b->status }}</span>
                    @if($b->payment_claimed_at)
                        <br><small class="text-muted">{{ ___('label.payment_claimed_awaiting') }}</small>
                    @endif
                </td>
                <td>
                    @if($payable)
                        <button type="button" class="btn btn-sm btn-primary"
                                data-toggle="modal" data-target="#payhotel_{{ $b->id }}">
                            {{ ___('label.pay_now') }}
                        </button>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">{{ ___('label.no_hotel_bookings_yet') }}</td></tr>
        @endforelse
    </x-data-table>

    @foreach($bookings as $b)
        @if(! in_array($b->status, ['Paid', 'Cancelled'], true) && (float) $b->amount > 0)
            <div class="modal fade" id="payhotel_{{ $b->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form method="POST" action="{{ route('cust.hotels.pay') }}" class="modal-content">
                        @csrf
                        <input type="hidden" name="id" value="{{ $b->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ ___('label.pay') }} {{ currency_symbol() }}{{ number_format($b->amount, 2) }} — {{ $b->booking_no }}</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-0">
                                <label class="label-style-1" for="hmethod_{{ $b->id }}">{{ ___('label.payment_method') }}</label>
                                <select id="hmethod_{{ $b->id }}" name="method" class="form-control input-style-1" required>
                                    @foreach($methods as $m)
                                        <option value="{{ $m }}">{{ $m === 'Wallet' ? ___('label.my_wallet') : $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="j-td-btn btn-red" data-dismiss="modal">{{ ___('label.cancel') }}</button>
                            <button type="submit" class="j-td-btn">{{ ___('label.pay') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

</x-page>
@endsection

@push('scripts')
{{-- Same hotel→room filter the back-office booking form uses (matches on
     #hotel_id / #hotel_room_id and each option's data-hotel-id). --}}
<script src="{{ asset('backend/js/custom/hotel_booking_room_filter.js') }}"></script>
@endpush
