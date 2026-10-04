@extends('backend.partials.master')
@section('title') Lead Details @endsection
@section('maincontent')
<x-page title="Lead Details" :breadcrumb="['CRM','Leads','Details']">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tv-card"><div class="tv-card-body text-center">
                <span class="tv-avatar tv-avatar-96 mx-auto mb-2">{{ strtoupper(mb_substr($lead->name, 0, 1)) }}</span>
                <h4 class="mb-0">{{ $lead->name }}</h4>
                {!! $lead->stageBadge() !!}
                <hr>
                <ul class="list-unstyled text-left">
                    <li class="mb-2"><i class="fa fa-phone text-muted mr-2"></i> {{ $lead->phone ?: '—' }}</li>
                    <li class="mb-2"><i class="fa fa-envelope text-muted mr-2"></i> {{ $lead->email ?: '—' }}</li>
                    <li class="mb-2"><i class="fa fa-bullhorn text-muted mr-2"></i> Source: {{ $lead->source ?: '—' }}</li>
                    <li class="mb-2"><i class="fa fa-suitcase text-muted mr-2"></i> Interest: {{ $lead->interest ?: '—' }}</li>
                    <li class="mb-2"><i class="fa fa-user-tie text-muted mr-2"></i> Owner: {{ $lead->owner ?: ($lead->assignedTo->name ?? '—') }}</li>
                    <li class="mb-0"><i class="fa fa-money-bill text-muted mr-2"></i> Value: {{ currency_symbol() }}{{ number_format($lead->value) }}</li>
                </ul>
                @if($lead->notes)
                <hr>
                <p class="text-muted text-left mb-0">{{ $lead->notes }}</p>
                @endif
                <div class="d-flex justify-content-center mt-3 tv-action-gap-md">
                    @if($lead->phone)
                        <a href="tel:{{ $lead->phone }}" class="btn btn-sm btn-primary"><i class="fa fa-phone"></i> Call</a>
                    @endif
                    @if($lead->email)
                        <a href="mailto:{{ $lead->email }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-envelope"></i> Email</a>
                    @endif
                    @if(hasPermission('crm_update') && str_contains(strtolower($lead->interest ?? ''), 'flight'))
                        <a href="{{ route('crm.leads.convert.flight', $lead->id) }}" class="btn btn-sm btn-success"><i class="fa fa-plane"></i> Convert to Booking</a>
                    @endif
                    @if(hasPermission('crm_update') && str_contains(strtolower($lead->interest ?? ''), 'hotel'))
                        <a href="{{ route('crm.leads.convert.hotel', $lead->id) }}" class="btn btn-sm btn-success"><i class="fa fa-hotel"></i> Convert to Hotel Booking</a>
                    @endif
                    @if(hasPermission('crm_update'))
                        <a href="{{ route('crm.leads.edit', $lead->id) }}" class="btn btn-sm btn-outline-success"><i class="fa fa-edit"></i> Edit</a>
                    @endif
                </div>
            </div></div>
        </div>
        <div class="col-lg-8 mb-4">
            <div class="tv-card h-100"><div class="tv-card-head"><h4 class="title-site mb-0">Activity Timeline</h4></div>
                <div class="tv-card-body">
                    <ul class="list-unstyled">
                        @php
                            $iconMap = [
                                'Call'      => ['fa-phone', 'primary'],
                                'Email'     => ['fa-envelope', 'info'],
                                'WhatsApp'  => ['fa-comment-dots', 'success'],
                                'Meeting'   => ['fa-user', 'warning'],
                                'SMS'       => ['fa-comment', 'secondary'],
                            ];
                        @endphp
                        @forelse($activities as $a)
                        @php [$icon, $tone] = $iconMap[$a->channel] ?? ['fa-flag', 'secondary']; @endphp
                        <li class="d-flex mb-4">
                            <div class="mr-3"><span class="tv-circle-icon-38 bg-{{ $tone }} text-white"><i class="fa-solid {{ $icon }}"></i></span></div>
                            <div>
                                <div class="d-flex justify-content-between"><b>{{ $a->subject }}</b></div>
                                @if($a->body)
                                    <div class="text-muted tv-text-sm">{{ $a->body }}</div>
                                @endif
                                <small class="text-muted">
                                    {{ $a->activity_date?->format('M j, Y') }}
                                    @if($a->user) &middot; {{ $a->user->name }} @endif
                                </small>
                            </div>
                        </li>
                        @empty
                        <li class="text-muted mb-4">No activity logged yet.</li>
                        @endforelse
                        <li class="d-flex">
                            <div class="mr-3"><span class="tv-circle-icon-38 bg-secondary text-white"><i class="fa-solid fa-flag"></i></span></div>
                            <div>
                                <div class="d-flex justify-content-between"><b>Lead created</b></div>
                                <div class="text-muted tv-text-sm">Came in via {{ $lead->source ?: 'an unknown source' }}.</div>
                                <small class="text-muted">{{ $lead->created_at->format('M j, Y') }}</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
