@extends('backend.partials.master')
@section('title') {{ ___('label.booking_details') }} @endsection
@section('maincontent')
@php $pending = $booking->status === 'pending'; @endphp
<x-page title="{{ ___('label.booking_details') }}" :breadcrumb="[___('menus.agent_portal'), ___('permissions.bookings'), ___('label.details')]">
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="tv-card h-100"><div class="tv-card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h4 class="mb-0">BKG-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</h4>
                    {!! $booking->statusBadge() !!}
                </div>

                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fa fa-suitcase text-muted mr-2"></i> {{ $booking->package->title ?? ___('label.no_package_fallback') }}</li>
                    <li class="mb-2"><i class="fa fa-user text-muted mr-2"></i> {{ $booking->customer_name }}</li>
                    <li class="mb-2"><i class="fa fa-phone text-muted mr-2"></i> {{ $booking->customer_phone ?: '—' }}</li>
                    <li class="mb-2"><i class="fa fa-envelope text-muted mr-2"></i> {{ $booking->customer_email ?: '—' }}</li>
                    <li class="mb-2"><i class="fa fa-calendar-days text-muted mr-2"></i> {{ $booking->travel_date?->format('d M Y') ?: '—' }}</li>
                    <li class="mb-2"><i class="fa fa-users text-muted mr-2"></i> {{ $booking->travelers }} {{ ___('label.travellers_suffix') }}</li>
                    <li class="mb-0"><i class="fa fa-money-bill text-muted mr-2"></i> {{ currency_symbol() }}{{ number_format($booking->amount) }}</li>
                </ul>

                @if($booking->notes)
                    <hr><p class="text-muted mb-0">{{ $booking->notes }}</p>
                @endif

                <div class="d-flex mt-3 tv-action-gap-md">
                    @if($booking->customer_phone)
                        <a href="tel:{{ $booking->customer_phone }}" class="btn btn-sm btn-primary"><i class="fa fa-phone"></i> {{ ___('label.call') }}</a>
                    @endif
                    {{-- A paid or confirmed booking is the office's to unwind:
                         cancelling it moves money and reverses a commission. --}}
                    @if($pending)
                        <form method="POST" action="{{ route('agent.booking.cancel', $booking->id) }}"
                              onsubmit="return confirm('{{ ___('label.cancel_this_booking_confirm') }}');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-ban"></i> {{ ___('label.cancel_booking') }}</button>
                        </form>
                    @endif
                </div>
            </div></div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="tv-card mb-4">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.amend') }}</h4></div>
                <div class="tv-card-body">
                    @if($pending)
                        {{-- The fare is re-quoted from the package on save, so
                             there is no amount field to argue with. --}}
                        <form method="POST" action="{{ route('agent.booking.update', $booking->id) }}">
                            @csrf @method('PUT')
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="label-style-1" for="travel_date">{{ ___('label.travel_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" id="travel_date" name="travel_date" class="form-control input-style-1"
                                           value="{{ old('travel_date', $booking->travel_date?->toDateString()) }}">
                                    @error('travel_date') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="label-style-1" for="travelers">{{ ___('menus.travelers') }} <span class="text-danger">*</span></label>
                                    <input type="number" min="1" max="50" id="travelers" name="travelers" class="form-control input-style-1"
                                           value="{{ old('travelers', $booking->travelers) }}">
                                    @error('travelers') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <button type="submit" class="j-td-btn">{{ ___('label.save_changes') }}</button>
                        </form>
                    @else
                        <p class="text-muted mb-0">
                            {{ str_replace([':status'], [$booking->status], ___('label.booking_status_office_handles')) }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="tv-card mb-4">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('menus.billing') }}</h4></div>
                <div class="tv-card-body">
                    @if($invoice)
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><b>{{ $invoice->invoice_no }}</b> — {{ ucfirst($invoice->status) }}</li>
                            <li class="mb-2">{{ ___('label.invoiced') }}: {{ currency_symbol() }}{{ number_format($invoice->amount, 2) }}</li>
                            <li class="mb-2">{{ ___('label.paid') }}: {{ currency_symbol() }}{{ number_format($invoice->paid_amount, 2) }}</li>
                            <li class="mb-0">{{ ___('label.due') }}: {{ currency_symbol() }}{{ number_format(max(0, $invoice->dueAmount()), 2) }}</li>
                        </ul>
                    @else
                        <p class="text-muted mb-0">{{ ___('label.not_invoiced_yet_hint') }}</p>
                    @endif
                </div>
            </div>

            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.my_commission') }}</h4></div>
                <div class="tv-card-body">
                    @if($commission)
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><b>{{ $commission->reference }}</b> — {{ ucfirst($commission->status) }}</li>
                            <li class="mb-2">{{ ___('label.rate') }}: {{ rtrim(rtrim(number_format($commission->rate, 2), '0'), '.') }}%</li>
                            <li class="mb-0">{{ ___('label.amount') }}: {{ currency_symbol() }}{{ number_format($commission->amount, 2) }}</li>
                        </ul>
                    @else
                        <p class="text-muted mb-0">{{ ___('label.commission_worked_out_hint') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
