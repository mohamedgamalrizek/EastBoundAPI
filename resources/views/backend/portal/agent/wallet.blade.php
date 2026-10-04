@extends('backend.partials.master')
@section('title') {{ ___('menus.agent_wallet') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.agent_wallet') }}" :breadcrumb="[___('menus.agent_portal'), ___('menus.wallet')]">

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.balance') }}</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($balance, 2) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.available_to_withdraw') }}</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($available, 2) }}</h3><small class="text-muted">{{ ___('label.balance_less_pending_requests') }}</small></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.withdrawn') }}</div><h3 class="mb-0 text-danger tv-fw-700">{{ currency_symbol() }}{{ number_format($withdrawn, 2) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.commission_pending_approval') }}</div><h3 class="mb-0 text-warning tv-fw-700">{{ currency_symbol() }}{{ number_format($pending, 2) }}</h3></div></div></div>
    </div>

    {{-- Requesting is the agent's half of the settlement: the office approves
         and marks it paid, and only then does the wallet go down. --}}
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.request_a_withdrawal') }}</h4></div>
                <div class="tv-card-body">
                    @if($available <= 0)
                        <p class="text-muted mb-0">
                            {{ str_replace([':amount'], [currency_symbol() . number_format($pending, 2)], ___('label.nothing_to_withdraw_hint')) }}
                        </p>
                    @else
                    <form method="POST" action="{{ route('agent.wallet.withdraw') }}">
                        @csrf
                        <div class="form-group">
                            <label class="label-style-1" for="amount">{{ ___('label.amount') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" max="{{ $available }}" id="amount" name="amount"
                                   class="form-control input-style-1" placeholder="0.00" value="{{ old('amount') }}">
                            <small class="text-muted">{{ ___('label.up_to') }} {{ currency_symbol() }}{{ number_format($available, 2) }}</small>
                            @error('amount') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label class="label-style-1" for="method">{{ ___('label.method') }} <span class="text-danger">*</span></label>
                            <select id="method" name="method" class="form-control input-style-1">
                                @foreach($methods as $m)
                                    <option value="{{ $m }}" @selected(old('method') === $m)>{{ $m }}</option>
                                @endforeach
                            </select>
                            @error('method') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label class="label-style-1" for="account_details">{{ ___('label.send_to') }} <span class="text-danger">*</span></label>
                            <input type="text" id="account_details" name="account_details" class="form-control input-style-1"
                                   placeholder="{{ ___('label.send_to_example') }}" value="{{ old('account_details') }}">
                            @error('account_details') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                        </div>
                        <button type="submit" class="j-td-btn">{{ ___('label.request_withdrawal') }}</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.my_withdrawal_requests') }}</h4></div>
                <div class="tv-card-body table-responsive">
                    <table class="table table-hover">
                        <thead class="bg"><tr><th>{{ ___('label.reference') }}</th><th>{{ ___('label.requested') }}</th><th>{{ ___('label.amount') }}</th><th>{{ ___('label.method') }}</th><th>{{ ___('label.status') }}</th></tr></thead>
                        <tbody>
                            @forelse($withdrawals as $w)
                                @php
                                    $badge = match($w->status) {
                                        'paid'     => 'success',
                                        'approved' => 'info',
                                        'rejected' => 'danger',
                                        default    => 'warning',
                                    };
                                @endphp
                                <tr>
                                    <td><b>{{ $w->reference }}</b></td>
                                    <td>{{ $w->requested_on?->format('d M Y') }}</td>
                                    <td>{{ currency_symbol() }}{{ number_format($w->amount, 2) }}</td>
                                    <td>{{ $w->method }}</td>
                                    <td><span class="bullet-badge bullet-badge-{{ $badge }}">{{ ucfirst($w->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center">{{ ___('label.no_withdrawal_requests_yet') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-data-table :headers="[___('label.reference'), ___('label.date'), ___('label.description'), ___('label.type'), ___('label.amount'), ___('label.balance')]">
        @foreach($transactions as $t)
            @php $cls = $t->type === 'credit' ? 'success' : 'danger'; $sign = $t->type === 'credit' ? '+' : '-'; @endphp
            <tr>
                <td><b>{{ $t->reference }}</b></td>
                <td>{{ $t->txn_date?->format('d M Y') }}</td>
                <td>{{ $t->description }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $cls }}">{{ ucfirst($t->type) }}</span></td>
                <td>{{ $sign }}{{ currency_symbol() }}{{ number_format($t->amount, 2) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($t->balance_after, 2) }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
