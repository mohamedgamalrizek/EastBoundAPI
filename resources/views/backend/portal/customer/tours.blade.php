@extends('backend.partials.master')
@section('title') {{ ___('menus.tours') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.tours') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.tours')]">

    @if($points['balance'] > 0 && $points['rate'] > 0)
        <div class="alert alert-info">
            You have <b>{{ number_format($points['balance']) }} points</b>
            (worth {{ currency_symbol() }}{{ number_format($points['balance'] * $points['rate'], 2) }}).
            Spend them on any booking below — up to {{ $points['max_percent'] }}% of the price,
            from {{ number_format($points['min']) }} points at a time.
        </div>
    @endif

    {{-- Active catalogue with a real Book action; the customer's own bookings
         (and the Pay button) live on the Bookings page. --}}
    <x-data-table :headers="[___('label.package'), ___('label.destination'), ___('label.category'), ___('label.duration'), ___('label.rating'), ___('label.price_per_person'), ___('label.book')]">
        @forelse($packages as $p)
            <tr>
                <td><b>{{ $p->title }}</b></td>
                <td>{{ $p->destination }}</td>
                <td>{{ $p->category }}</td>
                <td>{{ $p->duration }}</td>
                <td>
                    @if($p->avg_rating)
                        <span class="text-warning">&#9733;</span> {{ $p->avg_rating }}
                        <small class="text-muted">({{ $p->reviews_count }})</small>
                    @else
                        <small class="text-muted">No reviews yet</small>
                    @endif
                </td>
                <td>{{ currency_symbol() }}{{ number_format($p->price) }}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary"
                            data-toggle="modal" data-target="#book_{{ $p->id }}">
                        {{ ___('label.book') }}
                    </button>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ ___('label.no_tours_available') }}</td></tr>
        @endforelse
    </x-data-table>

    @foreach($packages as $p)
        <div class="modal fade" id="book_{{ $p->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form method="POST" action="{{ route('cust.tours.book') }}" class="modal-content tv-book-form"
                      data-package="{{ $p->id }}" data-price="{{ (float) $p->price }}">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $p->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ ___('label.book') }} {{ $p->title }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="label-style-1" for="travel_date_{{ $p->id }}">{{ ___('label.travel_date') }}</label>
                            <input type="date" id="travel_date_{{ $p->id }}" name="travel_date"
                                   class="form-control input-style-1" min="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="form-group">
                            <label class="label-style-1" for="travelers_{{ $p->id }}">{{ ___('menus.travelers') }}</label>
                            <input type="number" id="travelers_{{ $p->id }}" name="travelers" data-role="travelers"
                                   class="form-control input-style-1" min="1" max="50" value="1" required>
                        </div>

                        <div class="form-group">
                            <label class="label-style-1" for="coupon_{{ $p->id }}">Promo code <small class="text-muted">(optional)</small></label>
                            <input type="text" id="coupon_{{ $p->id }}" name="coupon_code" data-role="coupon"
                                   class="form-control input-style-1" placeholder="e.g. SUMMER20" autocomplete="off">
                        </div>

                        @if($points['balance'] > 0 && $points['rate'] > 0)
                            <div class="form-group">
                                <label class="label-style-1" for="points_{{ $p->id }}">
                                    Redeem points <small class="text-muted">(you have {{ number_format($points['balance']) }})</small>
                                </label>
                                <input type="number" id="points_{{ $p->id }}" name="points_redeemed" data-role="points"
                                       class="form-control input-style-1" min="0" step="1" value="0"
                                       max="{{ $points['balance'] }}" autocomplete="off">
                            </div>
                        @endif

                        {{-- Filled by the quote endpoint; the server prices the
                             booking, the browser only displays the answer. --}}
                        <div class="tv-quote border rounded p-3" data-role="quote">
                            <div class="d-flex justify-content-between"><span>{{ ___('label.price_per_person') }}</span>
                                <span>{{ currency_symbol() }}{{ number_format($p->price, 2) }}</span></div>
                            <div class="d-flex justify-content-between mt-1"><span>Subtotal</span>
                                <span data-line="gross">{{ currency_symbol() }}{{ number_format($p->price, 2) }}</span></div>
                            <div class="d-flex justify-content-between mt-1 text-success" data-line="coupon-row" hidden>
                                <span>Promo code</span><span data-line="coupon">—</span></div>
                            <div class="d-flex justify-content-between mt-1 text-success" data-line="points-row" hidden>
                                <span>Points</span><span data-line="points">—</span></div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between"><b>{{ ___('label.total') }}</b>
                                <b data-line="total">{{ currency_symbol() }}{{ number_format($p->price, 2) }}</b></div>
                            <small class="text-danger d-block mt-2" data-line="error" hidden></small>
                            <small class="text-muted d-block mt-2">{{ ___('label.pay_after_booking_total_hint') }}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="j-td-btn btn-red" data-dismiss="modal">{{ ___('label.cancel') }}</button>
                        <button type="submit" class="j-td-btn">{{ ___('label.book_now') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

</x-page>
@endsection

@push('scripts')
<script>
/**
 * Live price for the Book modal.
 *
 * The browser never works the discount out for itself — it asks the same
 * BookingPricing the booking will be written with, so what the customer is
 * shown and what they are charged cannot drift apart.
 */
(function () {
    var URL   = @json(route('cust.tours.quote'));
    var TOKEN = @json(csrf_token());
    var SYM   = @json(currency_symbol());

    function money(v) { return SYM + Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    function render(form, data) {
        var box = form.querySelector('[data-role="quote"]');
        var line = function (name) { return box.querySelector('[data-line="' + name + '"]'); };

        line('gross').textContent = money(data.gross_amount);
        line('total').textContent = money(data.amount);

        var couponRow = line('coupon-row');
        couponRow.hidden = !(data.coupon_discount > 0);
        line('coupon').textContent = '− ' + money(data.coupon_discount);

        var pointsRow = line('points-row');
        pointsRow.hidden = !(data.points_discount > 0);
        line('points').textContent = '− ' + money(data.points_discount) + ' (' + data.points_redeemed + ' pts)';

        var problem = data.error || data.coupon_error || data.points_error || '';
        var err = line('error');
        err.hidden = !problem;
        err.textContent = problem;
    }

    function quote(form) {
        var body = new FormData();
        body.append('_token', TOKEN);
        body.append('package_id', form.querySelector('[name="package_id"]').value);
        body.append('travelers', (form.querySelector('[data-role="travelers"]') || {}).value || 1);
        body.append('coupon_code', (form.querySelector('[data-role="coupon"]') || {}).value || '');
        body.append('points_redeemed', (form.querySelector('[data-role="points"]') || {}).value || 0);

        fetch(URL, { method: 'POST', body: body, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) { render(form, d); })
            // A failed quote must not block the booking: the server prices it
            // again on submit anyway, so the form simply keeps its last figure.
            .catch(function () {});
    }

    var timer;
    document.querySelectorAll('.tv-book-form').forEach(function (form) {
        form.addEventListener('input', function (e) {
            if (!e.target.matches('[data-role="travelers"], [data-role="coupon"], [data-role="points"]')) return;
            clearTimeout(timer);
            timer = setTimeout(function () { quote(form); }, 350);
        });
    });
})();
</script>
@endpush
