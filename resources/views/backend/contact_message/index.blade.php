@extends('backend.partials.master')
@section('title') Contact Messages @endsection
@section('maincontent')
<x-page title="Contact Messages" :breadcrumb="['CRM','Contact Messages']">

    <x-data-table :headers="['Name','Contact','Subject','Message','Status','Submitted','Action']">
        @forelse($messages as $message)
            <tr id="row_{{ $message->id }}">
                <td><b>{{ $message->name }}</b></td>
                <td>
                    @if($message->email)
                        <div><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></div>
                    @endif
                    @if($message->phone)
                        <div><a href="tel:{{ preg_replace('/[^\d+]/', '', $message->phone) }}">{{ $message->phone }}</a></div>
                    @endif
                    @if(!$message->email && !$message->phone)
                        <span class="text-muted">No contact</span>
                    @endif
                </td>
                <td>{{ $message->subject ?: 'General enquiry' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($message->message, 80) }}</td>
                <td>{!! $message->statusBadge() !!}</td>
                <td>{{ $message->created_at?->format('d M Y, h:i A') }}</td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('crm.contact-messages.show', $message->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.view') }}"><i class="fa fa-eye"></i></a>
                        @if(hasPermission('crm_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('crm.contact-messages.delete', $message->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $message->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    @if($messages->count())
        <x-paginate-show :items="$messages" />
    @endif

</x-page>
@endsection
