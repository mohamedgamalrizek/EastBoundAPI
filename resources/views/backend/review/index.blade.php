@extends('backend.partials.master')
@section('title') Reviews @endsection
@section('maincontent')
<x-page title="Reviews" :breadcrumb="['Reviews']">

    @if(!empty($analytics))
        <x-list-analytics title="Reviews overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Tour','Customer','Rating','Review','Status','Date','Action']">
        @foreach($items as $r)
            <tr id="row_{{ $r->id }}">
                <td>
                    <b>{{ $r->package->title ?? '—' }}</b>
                    @if($r->isVerified())
                        <br><small class="text-success" title="Written against a paid booking">&#10003; Verified &middot; BKG-{{ str_pad($r->booking_id, 5, '0', STR_PAD_LEFT) }}</small>
                    @else
                        <br><small class="text-muted">Added by staff</small>
                    @endif
                </td>
                <td>{{ $r->customer->name ?? '—' }}</td>
                <td><span class="text-warning">{{ str_repeat('★', $r->rating) }}</span> {{ $r->rating }}/5</td>
                <td>
                    @if($r->title)<b>{{ $r->title }}</b><br>@endif
                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($r->comment, 120) }}</small>
                    @if($r->reply)<br><small class="text-info"><b>Replied:</b> {{ \Illuminate\Support\Str::limit($r->reply, 80) }}</small>@endif
                </td>
                <td>{!! $r->statusBadge() !!}</td>
                <td>{{ $r->created_at->format('d M Y') }}</td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('review_update'))
                            @if($r->status !== \App\Models\Review::APPROVED)
                                <form method="POST" action="{{ route('review.approve', $r->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Approve"><i class="fa fa-check"></i></button>
                                </form>
                            @endif
                            @if($r->status !== \App\Models\Review::REJECTED)
                                <form method="POST" action="{{ route('review.reject', $r->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Reject"><i class="fa fa-times"></i></button>
                                </form>
                            @endif
                            <a href="{{ route('review.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Moderate &amp; reply"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('review_delete'))
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('review.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="Delete" data-text="This action cannot be reversed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-page>
@endsection
