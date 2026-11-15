@extends('backend.partials.master')
@section('title', 'Deleted Leads')
@section('maincontent')
<x-page title="Deleted Leads" :breadcrumb="['CRM', 'Deleted Leads']">
    <x-slot:action>
        <a href="{{ route('crm.leads') }}" class="btn btn-outline-primary">Back to Leads</a>
    </x-slot:action>
    <div class="tv-card">
        <div class="tv-card-head"><h4 class="title-site mb-0">Deleted Leads</h4></div>
        <div class="tv-card-body">
            <div class="table-responsive">
                <table class="table table-responsive-sm">
                    <thead class="bg"><tr><th>Contact</th><th>Interest</th><th>Stage</th><th>Deleted At</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr id="row_{{ $lead->id }}">
                                <td>{{ $lead->name }}<div class="text-muted"><small>{{ $lead->phone ?: '—' }} / {{ $lead->email ?: '—' }}</small></div></td>
                                <td>{{ $lead->interest ?: '—' }}</td>
                                <td>{{ $lead->stage }}</td>
                                <td>{{ $lead->deleted_at?->format('Y-m-d H:i') }}</td>
                                <td><div class="d-flex align-items-center flex-wrap tv-action-gap">
                                    @if(hasPermission('lead_restore'))
                                        <form action="{{ route('crm.deleted-leads.restore', $lead->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Restore</button>
                                        </form>
                                    @endif
                                    @if(hasPermission('lead_force_delete'))
                                        <a href="{{ route('crm.deleted-leads.destroy', $lead->id) }}" class="btn btn-sm btn-outline-danger"
                                           onclick="tryDelete(event)" data-remove-id="row_{{ $lead->id }}"
                                           data-title="Permanently delete lead?" data-text="This permanently deletes the lead and its activities. This action cannot be reversed."
                                           data-confirm-button-text="Delete permanently" data-cancel-button-text="Cancel">Delete permanently</a>
                                    @endif
                                </div></td>
                            </tr>
                        @empty
                            <x-nodata-found :colspan="5" />
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(count($leads))
                <x-paginate-show :items="$leads" />
            @endif
        </div>
    </div>
</x-page>
@endsection
