@extends('backend.partials.master')
@section('title', ___('menus.loyalty_settings'))
@section('maincontent')
<div class="container-fluid dashboard-content"><div class="tv-card"><div class="card-header"><h4 class="title-site">{{ ___('menus.loyalty_settings') }}</h4><x-how-it-works /></div><div class="tv-card-body">
    <form action="{{ route('settings.update') }}" method="POST">@csrf @method('PUT')
        <div class="settings-form-row">
            <section class="settings-section">
                <div class="settings-section__head">
                    <div>
                        <h5>Earning points</h5>
                        <p>Points a customer collects from paid bookings, and the bonus for a successful referral.</p>
                    </div>
                    <span class="settings-section__tag">Rewards</span>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="label-style-1" for="loyalty_points_per_currency">Loyalty points per currency</label>
                        <input type="number" step="0.001" min="0" class="form-control" id="loyalty_points_per_currency" name="loyalty_points_per_currency" value="{{ old('loyalty_points_per_currency', settings('loyalty_points_per_currency') ?: '0.01') }}">
                        <small class="text-muted">Points earned for each {{ currency_symbol() }}1 of a paid booking. At 0.01 a {{ currency_symbol() }}10,000 trip earns 100 points.</small>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="label-style-1" for="loyalty_referral_bonus">Referral bonus points</label>
                        <input type="number" min="0" class="form-control" id="loyalty_referral_bonus" name="loyalty_referral_bonus" value="{{ old('loyalty_referral_bonus', settings('loyalty_referral_bonus') ?: '100') }}">
                        <small class="text-muted">Points awarded to the referrer after a referred customer signs up.</small>
                    </div>
                </div>
            </section>

            <section class="settings-section">
                <div class="settings-section__head">
                    <div>
                        <h5>Spending points</h5>
                        <p>
                            Points are redeemed as a discount when booking a tour — never paid into the wallet,
                            which is a real liability account the ledger reconciles against money actually taken.
                            Set the rate to 0 to switch redemption off without touching anyone's balance.
                        </p>
                    </div>
                    <span class="settings-section__tag">Redemption</span>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="label-style-1" for="loyalty_redeem_rate">Value of one point ({{ currency_symbol() }})</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="loyalty_redeem_rate" name="loyalty_redeem_rate" value="{{ old('loyalty_redeem_rate', settings('loyalty_redeem_rate') ?? '1') }}">
                        <small class="text-muted">With the default earn rate, {{ currency_symbol() }}1 per point gives 1% back.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="label-style-1" for="loyalty_min_redeem">Minimum points per redemption</label>
                        <input type="number" min="0" class="form-control" id="loyalty_min_redeem" name="loyalty_min_redeem" value="{{ old('loyalty_min_redeem', settings('loyalty_min_redeem') ?? '100') }}">
                        <small class="text-muted">Stops one-point redemptions cluttering the statement.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="label-style-1" for="loyalty_max_redeem_percent">Maximum share of a booking (%)</label>
                        <input type="number" min="0" max="100" class="form-control" id="loyalty_max_redeem_percent" name="loyalty_max_redeem_percent" value="{{ old('loyalty_max_redeem_percent', settings('loyalty_max_redeem_percent') ?? '50') }}">
                        <small class="text-muted">Keeps some real money in every sale.</small>
                    </div>
                </div>
            </section>

            <section class="settings-section">
                <div class="settings-section__head">
                    <div>
                        <h5>Reviews</h5>
                        <p>A customer may only review a tour they paid for and have travelled on, so every review on the site is a verified booking.</p>
                    </div>
                    <span class="settings-section__tag">Reviews</span>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="label-style-1" for="review_auto_approve">Publish reviews automatically</label>
                        <select class="form-control" id="review_auto_approve" name="review_auto_approve">
                            <option value="0" @selected((string) settings('review_auto_approve') !== '1')>No — hold for moderation</option>
                            <option value="1" @selected((string) settings('review_auto_approve') === '1')>Yes — publish straight away</option>
                        </select>
                        <small class="text-muted">Held reviews wait in <b>Marketing &rarr; Reviews</b> until someone approves them.</small>
                    </div>
                </div>
            </section>
        </div>
        @if(hasPermission('general_settings_update'))<div class="j-create-btns mt-4"><div class="drp-btns"><button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button></div></div>@endif
    </form>
</div></div></div>
@endsection
