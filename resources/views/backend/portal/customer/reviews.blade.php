@extends('backend.partials.master')
@section('title') My Reviews @endsection
@section('maincontent')
<x-page title="My Reviews" :breadcrumb="[___('permissions.customer_portal'), 'My Reviews']">

    {{-- Trips that are paid for and taken but not yet reviewed. This is the
         prompt that actually produces reviews; a list on its own never does. --}}
    @if($pending->isNotEmpty())
        <div class="tv-card mb-4">
            <div class="card-header"><h4 class="title-site">How was your trip?</h4></div>
            <div class="tv-card-body">
                <div class="row">
                    @foreach($pending as $b)
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <b>{{ $b->package->title ?? 'Tour' }}</b><br>
                                    <small class="text-muted">
                                        BKG-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}
                                        &middot; {{ $b->travel_date?->format('d M Y') }}
                                    </small>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary"
                                        data-toggle="modal" data-target="#rev_{{ $b->id }}">Review</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <x-data-table :headers="['Tour', ___('label.rating'), 'Headline', ___('label.status'), ___('label.date')]">
        @forelse($reviews as $r)
            <tr>
                <td><b>{{ $r->package->title ?? '—' }}</b>
                    @if($r->isVerified())
                        <br><small class="text-success">&#10003; Verified booking</small>
                    @endif
                </td>
                <td><span class="text-warning">{{ str_repeat('★', $r->rating) }}</span></td>
                <td>
                    {{ $r->title ?: '—' }}
                    @if($r->comment)<br><small class="text-muted">{{ Str::limit($r->comment, 90) }}</small>@endif
                    @if($r->reply)
                        <br><small class="text-info"><b>Reply:</b> {{ Str::limit($r->reply, 90) }}</small>
                    @endif
                </td>
                <td>{!! $r->statusBadge() !!}</td>
                <td>{{ $r->created_at->format('d M Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">You have not reviewed a trip yet.</td></tr>
        @endforelse
    </x-data-table>

    @foreach($pending as $b)
        <div class="modal fade" id="rev_{{ $b->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form method="POST" action="{{ route('cust.reviews.store') }}" class="modal-content">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $b->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Review {{ $b->package->title ?? 'your trip' }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="label-style-1" for="rev_rating_{{ $b->id }}">{{ ___('label.rating') }}</label>
                            <select id="rev_rating_{{ $b->id }}" name="rating" class="form-control input-style-1" required>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ str_repeat('★', $i) }} ({{ $i }}/5)</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="label-style-1" for="rev_title_{{ $b->id }}">Headline <small class="text-muted">(optional)</small></label>
                            <input type="text" id="rev_title_{{ $b->id }}" name="title" maxlength="120"
                                   class="form-control input-style-1" placeholder="Sum it up in a few words">
                        </div>
                        <div class="form-group mb-0">
                            <label class="label-style-1" for="rev_comment_{{ $b->id }}">Your review</label>
                            <textarea id="rev_comment_{{ $b->id }}" name="comment" rows="4" maxlength="2000"
                                      class="form-control input-style-1" placeholder="How was the trip?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="j-td-btn btn-red" data-dismiss="modal">{{ ___('label.cancel') }}</button>
                        <button type="submit" class="j-td-btn">Submit review</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

</x-page>
@endsection
