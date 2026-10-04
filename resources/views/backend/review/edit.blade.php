@extends('backend.partials.master')
@section('title') Moderate Review @endsection
@section('maincontent')
<x-page title="Moderate Review" :breadcrumb="['Reviews','Moderate']">
    <div class="row">
        <div class="col-lg-5">
            {{-- What the customer wrote, shown read-only. The office moderates
                 a review; it does not rewrite one. --}}
            <div class="tv-card"><div class="tv-card-body">
                <h5 class="mb-1">{{ $item->package->title ?? '—' }}</h5>
                <p class="mb-2">
                    <span class="text-warning">{{ str_repeat('★', $item->rating) }}</span>
                    <span class="text-muted">{{ $item->rating }}/5 &middot; {{ $item->customer->name ?? '—' }}</span>
                </p>
                @if($item->isVerified())
                    <p class="text-success mb-2">
                        &#10003; Verified — booking BKG-{{ str_pad($item->booking_id, 5, '0', STR_PAD_LEFT) }}
                        @if($item->booking?->travel_date), travelled {{ $item->booking->travel_date->format('d M Y') }}@endif
                    </p>
                @else
                    <p class="text-muted mb-2">Not linked to a booking.</p>
                @endif
                @if($item->title)<h6>{{ $item->title }}</h6>@endif
                <p class="mb-0">{{ $item->comment ?: '—' }}</p>
                <hr>
                <small class="text-muted">Written {{ $item->created_at->format('d M Y, H:i') }}</small>
            </div></div>
        </div>

        <div class="col-lg-7">
            <div class="tv-card"><div class="tv-card-body">
                <form action="{{ route('review.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $item->id }}">

                    <div class="form-group">
                        <label class="label-style-1" for="status">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-control input-style-1 select2">
                            @foreach($status_options as $opt)
                                <option value="{{ $opt }}" @selected(old('status', $item->status) == $opt)>{{ ucfirst($opt) }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Only approved reviews appear on the website and count towards a tour's rating.</small>
                        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label class="label-style-1" for="reply">Public reply</label>
                        <textarea id="reply" name="reply" rows="5" class="form-control input-style-1"
                                  placeholder="Answer the traveller — shown under their review.">{{ old('reply', $item->reply) }}</textarea>
                        @error('reply') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                    </div>

                    <div class="j-create-btns">
                        <div class="drp-btns">
                            <button type="submit" class="j-td-btn">Save Changes</button>
                            <a href="{{ route('review.index') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
                        </div>
                    </div>
                </form>
            </div></div>
        </div>
    </div>
</x-page>
@endsection
