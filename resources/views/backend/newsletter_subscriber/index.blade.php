@extends('backend.partials.master')
@section('title') Newsletter Subscribers @endsection
@section('maincontent')
<x-page title="Newsletter Subscribers" :breadcrumb="['Marketing','Newsletter Subscribers']">

    <x-list-analytics title="Newsletter overview" :stats="$stats" />

    <x-data-table :headers="['Email','Source','Status','Subscribed At','Created','Action']">
        @forelse($subscribers as $subscriber)
            @php
                $statusClass = $subscriber->status === 'subscribed' ? 'success' : 'warning';
            @endphp
            <tr id="row_{{ $subscriber->id }}">
                <td><b><a href="mailto:{{ $subscriber->email }}">{{ $subscriber->email }}</a></b></td>
                <td>{{ $subscriber->source ?: 'website' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $statusClass }}">{{ ucfirst($subscriber->status) }}</span></td>
                <td>{{ $subscriber->subscribed_at?->format('d M Y, h:i A') ?: '-' }}</td>
                <td>{{ $subscriber->created_at?->format('d M Y, h:i A') }}</td>
                <td>
                    @if(hasPermission('campaign_delete'))
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('newsletter-subscriber.delete', $subscriber->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $subscriber->id }}" data-title="Delete" data-text="This action cannot be reversed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                    </div>
                    @endif
                </td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    @if($subscribers->count())
        <x-paginate-show :items="$subscribers" />
    @endif

</x-page>
@endsection

@push@endpush
