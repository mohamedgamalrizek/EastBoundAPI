@extends('backend.partials.master')
@section('title') Contact Message @endsection
@section('maincontent')
<x-page title="Contact Message" :breadcrumb="['CRM','Contact Messages','Details']">
    <x-slot:action>
        <a href="{{ route('crm.contact-messages.index') }}" class="j-td-btn btn-red"><span>Back</span></a>
    </x-slot:action>

    <div class="row">
        <div class="col-lg-8">
            <div class="tv-card">
                <div class="tv-card-body">
                    <h4 class="title-site mb-3">{{ $message->subject ?: 'General enquiry' }}</h4>
                    <p class="tv-pre-wrap">{{ $message->message }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="tv-card">
                <div class="tv-card-body">
                    <table class="table mb-0">
                        <tr><th>Name</th><td>{{ $message->name }}</td></tr>
                        <tr><th>Email</th><td>@if($message->email)<a href="mailto:{{ $message->email }}">{{ $message->email }}</a>@else<span class="text-muted">N/A</span>@endif</td></tr>
                        <tr><th>Phone</th><td>@if($message->phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $message->phone) }}">{{ $message->phone }}</a>@else<span class="text-muted">N/A</span>@endif</td></tr>
                        <tr><th>Status</th><td>{!! $message->statusBadge() !!}</td></tr>
                        <tr><th>Submitted</th><td>{{ $message->created_at?->format('d M Y, h:i A') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
