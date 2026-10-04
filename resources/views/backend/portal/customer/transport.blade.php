@extends('backend.partials.master')
@section('title') {{ ___('label.transport_bookings') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.transport_bookings') }}" :breadcrumb="['Customer Portal', ___('label.transport')]">

    {{-- Request-to-book, like the mobile app: the agency prices it on
         confirmation, then it can be paid. --}}
    <div class="tv-card mb-3"><div class="tv-card-body">
        <h6 class="mb-3">{{ ___('label.request_transport') }}</h6>
        <form method="POST" action="{{ route('cust.transport.book') }}">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label class="label-style-1" for="type">{{ ___('label.type') }}</label>
                    <select id="type" name="type" class="form-control input-style-1" required>
                        @foreach($types as $t)
                            <option value="{{ $t }}" @selected(old('type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-4">
                    <label class="label-style-1" for="route">{{ ___('label.route') }}</label>
                    <input type="text" id="route" name="route" class="form-control input-style-1"
                           placeholder="{{ ___('label.route_example_dhaka_coxsbazar') }}" value="{{ old('route') }}" required>
                    @error('route') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-2">
                    <label class="label-style-1" for="travel_date">{{ ___('label.travel_date') }}</label>
                    <input type="date" id="travel_date" name="travel_date" class="form-control input-style-1"
                           min="{{ now()->toDateString() }}" value="{{ old('travel_date') }}" required>
                    @error('travel_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-2">
                    <label class="label-style-1" for="vehicle">{{ ___('label.vehicle') }} <small class="text-muted">{{ ___('label.optional') }}</small></label>
                    <input type="text" id="vehicle" name="vehicle" class="form-control input-style-1"
                           placeholder="AC Bus" value="{{ old('vehicle') }}">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="j-td-btn w-100">{{ ___('label.request') }}</button>
                </div>
            </div>
        </form>
    </div></div>

    <x-data-table :headers="[___('label.booking_no'), ___('label.type'), ___('label.route'), ___('label.travel_date'), ___('label.vehicle'), ___('label.driver'), ___('label.fare'), ___('label.status'), ___('label.action')]">
        @forelse($trips as $t)
            @php
                $c = match($t->status) {
                    'Paid', 'Completed' => 'success',
                    'Confirmed'         => 'info',
                    'Cancelled'         => 'danger',
                    default             => 'warning',
                };
                $payable = ! in_array($t->status, ['Paid', 'Completed', 'Cancelled'], true) && (float) $t->fare > 0;
            @endphp
            <tr>
                <td><b>{{ $t->booking_no }}</b></td>
                <td>{{ $t->type }}</td>
                <td>{{ $t->route }}</td>
                <td>{{ $t->travel_date?->format('d M Y') }}</td>
                <td>{{ $t->vehicle ?? '—' }}</td>
                <td>{{ $t->driver->name ?? ___('label.not_assigned_yet') }}</td>
                {{-- A pending request has no fare yet — the agency prices it on confirmation. --}}
                <td>{{ (float) $t->fare > 0 ? currency_symbol() . number_format($t->fare, 2) : ___('label.to_be_quoted') }}</td>
                <td>
                    <span class="bullet-badge bullet-badge-{{ $c }}">{{ $t->status }}</span>
                    @if($t->payment_claimed_at)
                        <br><small class="text-muted">{{ ___('label.payment_claimed_awaiting') }}</small>
                    @endif
                </td>
                <td>
                    @if($payable)
                        <button type="button" class="btn btn-sm btn-primary"
                                data-toggle="modal" data-target="#paytrip_{{ $t->id }}">
                            {{ ___('label.pay_now') }}
                        </button>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">{{ ___('label.no_transport_bookings_yet') }}</td></tr>
        @endforelse
    </x-data-table>

    @foreach($trips as $t)
        @if(! in_array($t->status, ['Paid', 'Completed', 'Cancelled'], true) && (float) $t->fare > 0)
            <div class="modal fade" id="paytrip_{{ $t->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form method="POST" action="{{ route('cust.transport.pay') }}" class="modal-content">
                        @csrf
                        <input type="hidden" name="id" value="{{ $t->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ ___('label.pay') }} {{ currency_symbol() }}{{ number_format($t->fare, 2) }} — {{ $t->booking_no }}</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-0">
                                <label class="label-style-1" for="tmethod_{{ $t->id }}">{{ ___('label.payment_method') }}</label>
                                <select id="tmethod_{{ $t->id }}" name="method" class="form-control input-style-1" required>
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
