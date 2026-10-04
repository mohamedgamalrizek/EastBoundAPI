@extends('backend.partials.master')
@section('title') Ticket Details @endsection
@section('maincontent')
<x-page title="Ticket Details" :breadcrumb="['Support','Ticket']">

@if($ticket)
    @php
        $pc = $ticket->priority === 'High' ? 'danger' : ($ticket->priority === 'Low' ? 'info' : 'warning');
        $sc = $ticket->status === 'Closed' ? 'success' : ($ticket->status === 'Pending' ? 'info' : 'warning');
    @endphp
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="tv-card">
                <div class="tv-card-head d-flex justify-content-between">
                    <h4 class="title-site mb-0">{{ $ticket->ticket_no }} · {{ $ticket->subject }}</h4>
                    <span class="bullet-badge bullet-badge-{{ $sc }}">{{ $ticket->status }}</span>
                </div>
                <div class="tv-card-body">
                    <div class="d-flex mb-3">
                        <div>
                            <b>{{ $ticket->customer_name }}</b>
                            <div class="text-muted small">{{ $ticket->created_at?->format('d M Y, h:i A') }}</div>
                            <p class="mb-0">{{ $ticket->subject }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="tv-card">
                <div class="tv-card-body">
                    <h6>Details</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><span class="text-muted">Priority:</span> <span class="bullet-badge bullet-badge-{{ $pc }}">{{ $ticket->priority }}</span></li>
                        <li class="mb-2"><span class="text-muted">Department:</span> {{ $ticket->department ?? '—' }}</li>
                        <li class="mb-2"><span class="text-muted">Customer:</span> {{ $ticket->customer_name }}</li>
                        <li class="mb-0"><span class="text-muted">Created:</span> {{ $ticket->created_at?->format('Y-m-d') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="tv-card"><div class="tv-card-body text-muted">No ticket found.</div></div>
@endif

</x-page>
@endsection
