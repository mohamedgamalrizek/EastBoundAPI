@extends('backend.partials.master')
@section('title')
Bookings
@endsection
@section('maincontent')
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">Bookings</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <x-list-analytics
                title="Bookings overview"
                :stats="$analytics['stats']"
                :donut="$analytics['donut']"
                :trend="$analytics['trend']" />
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="j-parcel-main j-parcel-res">
                <div class="tv-card">
                    <div class="tv-card-head mb-3">
                        <h4 class="title-site">Bookings</h4>
                        <x-how-it-works />
                        <a href="{{ route('booking.create') }}" class="j-td-btn">
                            <img src="{{ asset('backend') }}/icons/icon//plus-white.png" class="jj" alt="add"> <span>Add</span>
                        </a>
                    </div>

                    <div class="tv-card-body">
                        <x-data-table :card="false" :headers="['ID','Customer','Agent','Package','Travel Date','PAX','Amount','Status','Action']">
                                    @foreach($bookings as $booking)
                                    <tr id="row_{{ $booking->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div>{{ $booking->customer_name }}
                                                @if($booking->customer_id)
                                                <i class="fa fa-link text-success ml-1" title="Linked to a customer account"></i>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $booking->customer_phone }}</small>
                                        </td>
                                        <td>{{ $booking->agent->name ?? '—' }}</td>
                                        <td>{{ $booking->package->title ?? '—' }}</td>
                                        <td>{{ $booking->travel_date?->format('d M Y') }}</td>
                                        <td>{{ $booking->travelers }}</td>
                                        <td>{{ currency_symbol() }}{{ number_format($booking->amount) }}</td>
                                        <td>
                                            {!! $booking->statusBadge() !!}
                                            @if($booking->payment_claimed_at)
                                                <br><small class="text-muted" title="{{ $booking->payment_claimed_at }}">Payment claimed</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <div class="input-group-prepend be-addon">
                                                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                                                        <a href="{{ route('booking.edit', $booking->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                        <a class="btn btn-sm btn-outline-danger" href="{{ route('booking.delete', $booking->id) }}"
                                                            onclick="tryDelete(event)"
                                                            data-remove-id="row_{{ $booking->id }}"
                                                            data-title="Delete"
                                                            data-text="This action cannot be reversed."
                                                            data-confirm-button-text="Delete"
                                                            data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                        </x-data-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
