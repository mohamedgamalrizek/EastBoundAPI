@extends('backend.partials.master')
@section('title') Transport Booking Details @endsection
@section('maincontent')
@php
    $statusColor = in_array($booking->status, ['Confirmed', 'Completed'])
        ? 'success'
        : ($booking->status === 'Cancelled' ? 'danger' : 'warning');
@endphp
<x-page title="Transport Booking Details" :breadcrumb="['Transport', $booking->type, 'Details']">

    @if(hasPermission('transport_update'))
    <x-slot name="action">
        <a href="{{ route('transport.edit', $booking->id) }}" class="j-td-btn">
            <i class="fa fa-edit"></i> <span>{{ ___('label.edit') }}</span>
        </a>
    </x-slot>
    @endif

    <div class="row">
        <div class="col-lg-5 mb-4"><div class="tv-card"><div class="tv-card-body">
            <h4 class="mb-1">{{ $booking->booking_no }}</h4>
            <span class="bullet-badge bullet-badge-{{ $statusColor }}">{{ $booking->status }}</span>
            <hr>
            <ul class="list-unstyled mb-0">
                <li class="mb-2"><span class="text-muted">Type:</span> <b>{{ $booking->type }}{{ $booking->direction ? ' — ' . $booking->direction : '' }}</b></li>
                <li class="mb-2"><span class="text-muted">{{ ___('label.customer') }}:</span> {{ $booking->customer_name ?: ($booking->customer->name ?? '—') }}</li>
                <li class="mb-2"><span class="text-muted">Route:</span> {{ $booking->route ?: '—' }}</li>
                <li class="mb-2"><span class="text-muted">Travel Date:</span> {{ $booking->travel_date?->format('d M Y') ?? '—' }}</li>
                <li class="mb-2"><span class="text-muted">Vehicle:</span> {{ $booking->vehicle ?: '—' }}</li>
                <li class="mb-2"><span class="text-muted">Driver:</span> {{ $booking->driver->name ?? '—' }}</li>
                <li class="mb-0"><span class="text-muted">Fare:</span> <b>{{ currency_symbol() }}{{ number_format((float) $booking->fare) }}</b></li>
            </ul>
        </div></div></div>

        <div class="col-lg-7 mb-4">
            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">Booking Information</h4></div>
                <div class="tv-card-body">
                    <table class="table table-responsive-sm mb-0">
                        <tbody>
                            <tr><td class="text-muted">{{ ___('label.status') }}</td><td class="text-right"><span class="bullet-badge bullet-badge-{{ $statusColor }}">{{ $booking->status }}</span></td></tr>
                            <tr>
                                <td class="text-muted">Linked Package Booking</td>
                                <td class="text-right">
                                    @if($booking->booking)
                                        #{{ $booking->booking->id }} — {{ $booking->booking->package->title ?? 'No package' }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr><td class="text-muted">Customer Account</td><td class="text-right">{{ $booking->customer->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Created</td><td class="text-right">{{ $booking->created_at?->format('d M Y, h:i A') ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Last Updated</td><td class="text-right">{{ $booking->updated_at?->format('d M Y, h:i A') ?? '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
