@extends('backend.partials.master')
@section('title') Lead Attribution & Integration @endsection
@section('maincontent')
<x-page title="Lead Attribution & Integration" :breadcrumb="['DMC', 'Lead Tracking']">
    <div class="tv-card mb-4"><div class="tv-card-body">
        <h4>Website lead intake</h4>
        <p>Send POST requests to <code>{{ url('/api/v1/dmc/leads') }}</code> with the configured X-App-Key header and JSON fields: website (source identifier), name, email or phone, language, country, campaign_source, subject, and campaign_id for Google Ads. Creation date and time are captured automatically.</p>
        <p>Legacy integrations remain compatible. Their incomplete records appear below for explicit correction; no website, language or country is guessed.</p>
        <table class="table"><thead><tr><th>Website</th><th>Source identifier</th><th>Domain</th><th>Status</th></tr></thead><tbody>@foreach($websites as $website)<tr><td>{{ $website->name }}</td><td><code>{{ $website->slug }}</code></td><td>{{ $website->domain ?: 'Awaiting domain configuration' }}</td><td>{{ $website->active ? 'Active' : 'Inactive' }}</td></tr>@endforeach</tbody></table>
    </div></div>
    <h4>Incomplete historical attribution ({{ $incomplete->total() }})</h4>
    <div class="tv-card"><div class="tv-card-body"><table class="table"><thead><tr><th>Lead</th><th>Recorded website</th><th>Language</th><th>Country</th><th>Campaign source</th><th></th></tr></thead><tbody>
        @foreach($incomplete as $lead)<tr><td>{{ $lead->name }}</td><td>{{ $lead->website }}</td><td>{{ $lead->language }}</td><td>{{ $lead->country }}</td><td>{{ $lead->campaign_source }}</td><td>@if(hasPermission('crm_update'))<a href="{{ route('crm.leads.edit', $lead->id) }}">Correct attribution</a>@endif</td></tr>@endforeach
    </tbody></table>{{ $incomplete->links() }}</div></div>
</x-page>
@endsection
