@extends('backend.partials.master')
@section('title') Convert Lead to Hotel Booking @endsection
@section('maincontent')
<x-page title="Convert Lead to Hotel Booking" :breadcrumb="['CRM','Leads','Convert to Booking']">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="tv-card"><div class="tv-card-body">
                <div class="d-flex align-items-center">
                    <span class="tv-avatar tv-avatar-48 mr-3">{{ strtoupper(mb_substr($lead->name, 0, 1)) }}</span>
                    <div>
                        <h5 class="mb-0">{{ $lead->name }}</h5>
                        <small class="text-muted">
                            {{ $lead->phone ?: '—' }}
                            @if($lead->email) &middot; {{ $lead->email }} @endif
                            &middot; Interest: {{ $lead->interest }}
                        </small>
                    </div>
                    <a href="{{ route('crm.leads.show', $lead->id) }}" class="btn btn-sm btn-outline-primary ml-auto"><i class="fa fa-arrow-left"></i> Back to lead</a>
                </div>
                <hr>
                <p class="text-muted mb-0">
                    Pre-filled from this lead's enquiry — guest name, check-in/out dates and nights.
                    Pick the hotel, room and booking number to turn the request into a real booking.
                    The lead will be marked <b>Won</b> once saved.
                </p>
            </div></div>
        </div>
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('crm.leads.store.hotel', $lead->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                        @include('backend.hotel.booking._form', ['item' => $item, 'cancelRoute' => route('crm.leads.show', $lead->id)])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
