@extends('backend.partials.master')
@section('title') Leads @endsection
@section('maincontent')
<x-page title="Leads" :breadcrumb="['CRM','Leads']">
    <x-slot:action>
        <a href="{{ route('crm.leads.create') }}" class="btn btn-primary"><i class="fa fa-plus mr-1"></i> Add Lead</a>
    </x-slot:action>

    <div class="row"><div class="col-12">
        <div class="tv-card">
            <div class="tv-card-head"><h4 class="title-site mb-0">All Leads</h4></div>
            <div class="tv-card-body">
                <div class="table-responsive">
                    <table class="table table-responsive-sm">
                        <thead class="bg">
                            <tr><th>Contact</th><th>Interest</th><th>Source</th><th>Language / Country</th><th>CSource</th><th>Travel Dates</th><th>Travelers</th><th>Budget</th><th>Stage</th><th>Owner</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $key => $lead)
                            <tr id="row_{{ $lead->id }}">
                                <td>
                                    <a href="{{ route('crm.leads.show', $lead->id) }}">{{ $lead->name }}</a>
                                    <div class="text-muted"><small>{{ $lead->phone ?: '—' }}</small></div>
                                    <div class="text-muted"><small>{{ $lead->email ?: '—' }}</small></div>
                                </td>
                                <td>
                                    <div>{{ $lead->interest ?: '—' }}</div>
                                    <small class="d-block text-muted">Subject: {{ $lead->subject ?: '—' }}</small>
                                    <small class="d-block text-muted">Destinations: {{ $lead->destinations ?: '—' }}</small>
                                </td>
                                <td><div>{{ $lead->source }}</div><small class="text-muted">{{ $lead->website ?: '—' }}</small></td>
                                <td><div>{{ $lead->language ?: '—' }}</div><small class="text-muted">{{ $lead->country ?: '—' }}</small></td>
                                <td>{{ $lead->campaign_source ?: '—' }}</td>
                                <td>
                                    <div>{{ $lead->from_date?->format('Y-m-d') ?? '—' }} → {{ $lead->to_date?->format('Y-m-d') ?? '—' }}</div>
                                    <small class="text-muted">Days: {{ $lead->duration ?? '—' }}</small>
                                </td>
                                <td><div>Adults: {{ $lead->adults ?? '—' }}</div><small class="text-muted">Children: {{ $lead->children ?? '—' }}</small></td>
                                <td><div>{{ currency_symbol() }}{{ number_format($lead->value, 2) }}</div><small class="text-muted">Budget: {{ $lead->budget ?: '—' }}</small></td>
                                <td>{!! $lead->stageBadge() !!}</td>
                                <td>{{ $lead->owner ?? '—' }}</td>
                                <td>
                                    <div class="input-group">
                                        <div class="input-group-prepend be-addon">
                                            <div class="d-flex align-items-center flex-wrap tv-action-gap">
                                                <a href="{{ route('crm.leads.show', $lead->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="fa fa-eye"></i></a>
                                                <a href="{{ route('crm.leads.edit', $lead->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                <a class="btn btn-sm btn-outline-danger" href="{{ route('crm.leads.delete', $lead->id) }}"
                                                    onclick="tryDelete(event)"
                                                    data-remove-id="row_{{ $lead->id }}"
                                                    data-title="Delete" data-text="This action cannot be reversed."
                                                    data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <x-nodata-found :colspan="13" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(count($leads))
                <x-paginate-show :items="$leads" />
                @endif
            </div>
        </div>
    </div></div>
</x-page>
@endsection
