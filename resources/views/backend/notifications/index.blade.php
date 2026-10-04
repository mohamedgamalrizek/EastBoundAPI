@extends('backend.partials.master')
@section('title') Notifications @endsection
@section('maincontent')
<x-page title="Notifications" :breadcrumb="['Notifications']">

    <x-slot name="action">
        <button type="button" class="j-td-btn" id="markAllReadBtn">Mark all as read</button>
    </x-slot>

    <x-data-table :headers="['Title','Detail','Category','Received','Status','Action']">
        @forelse($notifications as $n)
            @php
                $cat = ['booking'=>'success','payment'=>'warning','visa'=>'info','flight'=>'info','general'=>'secondary'];
                $cc = $cat[$n->category] ?? 'secondary';
            @endphp
            <tr id="notification_row_{{ $n->id }}">
                <td><b>{{ $n->title }}</b></td>
                <td>{{ $n->body }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $cc }}">{{ ucfirst($n->category) }}</span></td>
                <td>{{ $n->created_at?->diffForHumans() }}</td>
                <td>
                    <span class="bullet-badge bullet-badge-{{ $n->is_read ? 'success' : 'warning' }}">
                        {{ $n->is_read ? 'Read' : 'Unread' }}
                    </span>
                </td>
                <td>
                    @unless($n->is_read)
                        <button type="button" class="btn btn-sm btn-outline-primary js-mark-read-page" data-id="{{ $n->id }}">
                            Mark read
                        </button>
                    @else
                        —
                    @endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No notifications yet.</td></tr>
        @endforelse
    </x-data-table>

</x-page>
@endsection
