@extends('backend.partials.master')
@section('title') {{ ___('menus.my_bookings') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.my_bookings') }}" :breadcrumb="[___('permissions.customer_portal'), ___('permissions.bookings')]">

    <x-data-table :headers="[___('label.booking'), ___('label.detail'), ___('label.travel_date'), ___('menus.travelers'), ___('label.amount'), ___('label.status'), ___('label.action')]">
        @foreach($bookings as $b)
            @php
                $map = ['pending'=>'warning','confirmed'=>'info','paid'=>'success','cancelled'=>'danger'];
                $c = $map[$b->status] ?? 'warning';
                $payable = in_array($b->status, ['pending', 'confirmed'], true) && (float) $b->amount > 0;
                $state        = $reviewState[$b->id] ?? ['review' => null, 'reason' => 'Not reviewable.'];
                $review       = $state['review'];
                $reviewReason = $state['reason'];
            @endphp
            <tr>
                <td><b>BKG-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</b></td>
                <td>{{ $b->package->title ?? $b->customer_name }}</td>
                <td>{{ $b->travel_date?->format('d M Y') }}</td>
                <td>{{ $b->travelers }}</td>
                <td>
                    {{ currency_symbol() }}{{ number_format($b->amount) }}
                    @if($b->hasDiscount())
                        <br><small class="text-muted"><del>{{ currency_symbol() }}{{ number_format($b->grossAmount()) }}</del></small>
                        <small class="text-success">
                            &minus;{{ currency_symbol() }}{{ number_format($b->totalDiscount()) }}
                            @if($b->coupon_code) ({{ $b->coupon_code }}) @endif
                        </small>
                    @endif
                </td>
                <td>
                    <span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($b->status) }}</span>
                    @if($b->payment_claimed_at)
                        <br><small class="text-muted">{{ ___('label.payment_claimed_awaiting') }}</small>
                    @endif
                </td>
                <td>
                    @if($payable)
                        <button type="button" class="btn btn-sm btn-primary"
                                data-toggle="modal" data-target="#pay_{{ $b->id }}">
                            {{ ___('label.pay_now') }}
                        </button>
                    @endif

                    {{-- Reviewable only once the trip is paid for and taken;
                         ReviewService decided that, not this template. --}}
                    @if($review)
                        <span class="bullet-badge bullet-badge-{{ $review->statusTone() }}">
                            {{ $review->rating }}&#9733; {{ ucfirst($review->status) }}
                        </span>
                    @elseif(! $reviewReason)
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-toggle="modal" data-target="#review_{{ $b->id }}">
                            Write a review
                        </button>
                    @elseif(! $payable)
                        —
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>

    @foreach($bookings as $b)
        @if(in_array($b->status, ['pending', 'confirmed'], true) && (float) $b->amount > 0)
            <div class="modal fade" id="pay_{{ $b->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form method="POST" action="{{ route('cust.bookings.pay') }}" class="modal-content">
                        @csrf
                        <input type="hidden" name="id" value="{{ $b->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ ___('label.pay') }} {{ currency_symbol() }}{{ number_format($b->amount, 2) }}</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-0">
                                <label class="label-style-1" for="method_{{ $b->id }}">{{ ___('label.payment_method') }}</label>
                                <select id="method_{{ $b->id }}" name="method" class="form-control input-style-1" required>
                                    @foreach($methods as $m)
                                        <option value="{{ $m }}">{{ $m === 'Wallet' ? ___('label.my_wallet') : $m }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">{{ ___('label.payment_confirmation_hint') }}</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="j-td-btn btn-red" data-dismiss="modal">{{ ___('label.cancel') }}</button>
                            <button type="submit" class="j-td-btn">{{ ___('label.pay') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    @foreach($bookings as $b)
        @php
            // A null reason means ReviewService found nothing standing in the
            // way; any string is the explanation for why it did.
            $state = $reviewState[$b->id] ?? null;
            $canReview = $state && ! $state['review'] && $state['reason'] === null;
        @endphp
        @if($canReview)
            <div class="modal fade" id="review_{{ $b->id }}" tabindex="-1" role="dialog">
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
                                <label class="label-style-1" for="rating_{{ $b->id }}">Rating</label>
                                <select id="rating_{{ $b->id }}" name="rating" class="form-control input-style-1" required>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}">{{ str_repeat('★', $i) }} ({{ $i }}/5)</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="label-style-1" for="title_{{ $b->id }}">Headline <small class="text-muted">(optional)</small></label>
                                <input type="text" id="title_{{ $b->id }}" name="title" maxlength="120"
                                       class="form-control input-style-1" placeholder="Sum it up in a few words">
                            </div>
                            <div class="form-group mb-0">
                                <label class="label-style-1" for="comment_{{ $b->id }}">Your review</label>
                                <textarea id="comment_{{ $b->id }}" name="comment" rows="4" maxlength="2000"
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
        @endif
    @endforeach

</x-page>
@endsection
