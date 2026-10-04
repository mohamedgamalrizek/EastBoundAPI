@extends('backend.partials.master')
@section('title')
Customer Details
@endsection
@section('maincontent')
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('customer.index') }}" class="breadcrumb-link active">Customers</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">Details</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    @if($customer)
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tv-card"><div class="tv-card-body text-center">
                <img src="{{ $customer->avatar ? asset($customer->avatar) : asset('backend/images/avatar/male-avatar-1.jpg') }}" class="rounded-circle mb-2 tv-img-96 tv-object-cover" width="96" height="96">
                <h4 class="mb-0">{{ $customer->name }}</h4>
                <div class="mt-1">{!! $customer->tierBadge() !!} {!! $customer->statusBadge() !!}</div>
                <hr>
                <ul class="list-unstyled text-left mb-0">
                    <li class="mb-2"><i class="fa fa-phone text-muted mr-2"></i> {{ $customer->phone ?: '—' }}</li>
                    <li class="mb-2"><i class="fa fa-envelope text-muted mr-2"></i> {{ $customer->email ?: '—' }}</li>
                    <li class="mb-0"><i class="fa fa-location-dot text-muted mr-2"></i> {{ $customer->address ?: '—' }}</li>
                </ul>
                <hr>
                <a href="{{ route('customer.edit', $customer->id) }}" class="j-td-btn">Edit</a>
            </div></div>
        </div>
        <div class="col-lg-8 mb-4">
            <div class="tv-card"><div class="tv-card-head"><h4 class="title-site mb-0">Profile</h4><x-how-it-works /></div>
                <div class="tv-card-body">
                    <table class="table table-responsive-sm mb-0">
                        <tbody>
                            <tr><th class="tv-w-180">Customer ID</th><td><b>CUS-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</b></td></tr>
                            <tr><th>Name</th><td>{{ $customer->name }}</td></tr>
                            <tr><th>Email</th><td>{{ $customer->email ?: '—' }}</td></tr>
                            <tr><th>Phone</th><td>{{ $customer->phone ?: '—' }}</td></tr>
                            <tr><th>Address</th><td>{{ $customer->address ?: '—' }}</td></tr>
                            <tr><th>Tier</th><td>{!! $customer->tierBadge() !!}</td></tr>
                            <tr><th>Status</th><td>{!! $customer->statusBadge() !!}</td></tr>
                            <tr><th>Notes</th><td>{{ $customer->notes ?: '—' }}</td></tr>
                            <tr><th>Joined</th><td>{{ $customer->created_at?->format('d M Y') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Side services used to live only in their own modules, so a customer's
         profile showed no sign that they had bought insurance or a medical
         trip — and nothing added up what those sales earned. --}}
    @php
        $services = collect()
            ->concat($customer->insurances->map(fn ($r) => [
                'module' => ___('menus.travel_insurance'),
                'detail' => trim($r->provider . ' · ' . $r->plan_name, ' ·'),
                'fee'    => $r->service_fee,
                'status' => $r->status,
            ]))
            ->concat($customer->studentServices->map(fn ($r) => [
                'module' => ___('menus.student_consultancy'),
                'detail' => trim($r->university . ' · ' . $r->country, ' ·') ?: $r->service_type,
                'fee'    => $r->service_fee,
                'status' => $r->status,
            ]))
            ->concat($customer->medicalTours->map(fn ($r) => [
                'module' => ___('menus.medical_tourism'),
                'detail' => trim($r->hospital . ' · ' . $r->destination, ' ·'),
                'fee'    => $r->service_fee,
                'status' => $r->status,
            ]))
            ->concat($customer->corporateTravels->map(fn ($r) => [
                'module' => ___('menus.corporate_travel'),
                'detail' => trim($r->company_name . ' · ' . $r->service_type, ' ·'),
                'fee'    => $r->service_fee,
                'status' => $r->status,
            ]));
    @endphp

    @if($services->count())
    <div class="row"><div class="col-12 mb-4"><div class="tv-card">
        <div class="tv-card-head">
            <h4 class="title-site mb-0">{{ ___('menus.services') }}</h4>
            <span class="text-muted tv-text-sm">
                {{ ___('label.service_revenue') }}: {{ currency_symbol() }}{{ number_format($services->sum('fee')) }}
            </span>
        </div>
        <div class="tv-card-body"><div class="table-responsive">
            <table class="table table-responsive-sm mb-0">
                <thead class="bg"><tr>
                    <th>{{ ___('menus.services') }}</th>
                    <th>{{ ___('label.details') }}</th>
                    <th>{{ ___('label.service_fee') }}</th>
                    <th>{{ ___('label.status') }}</th>
                </tr></thead>
                <tbody>
                    @foreach($services as $service)
                    <tr>
                        <td><b>{{ $service['module'] }}</b></td>
                        <td>{{ $service['detail'] ?: '—' }}</td>
                        <td>{{ $service['fee'] !== null ? currency_symbol() . number_format($service['fee']) : '—' }}</td>
                        <td>{{ ucfirst($service['status']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div></div>
    </div></div></div>
    @endif
    {{-- Wallet: money the agency is holding for this customer. Every line is
         either a top-up/adjustment made here, or a receipt/refund that already
         posted itself — so this and account 2300 always agree. --}}
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">Wallet</h4></div>
                <div class="tv-card-body">
                    <div class="text-muted tv-text-xs">Balance</div>
                    <h3 class="mb-4 tv-fw-700">{{ currency_symbol() }}{{ number_format($walletBalance ?? 0, 2) }}</h3>

                    @if(hasPermission('customer_update'))
                    <form method="POST" action="{{ route('customer.wallet.adjust', $customer->id) }}">
                        @csrf
                        <div class="form-group">
                            <label class="label-style-1" for="wallet_type">Action</label>
                            <select id="wallet_type" name="type" class="form-control input-style-1">
                                <option value="credit">Top up / credit</option>
                                <option value="debit">Deduct / adjust</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="label-style-1" for="wallet_amount">Amount</label>
                            <input type="number" step="0.01" min="0.01" id="wallet_amount" name="amount" class="form-control input-style-1" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="label-style-1" for="wallet_method">Received in / paid from</label>
                            <select id="wallet_method" name="method" class="form-control input-style-1">
                                @foreach(['Cash','Bank','Card','bKash','Nagad'] as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="label-style-1" for="wallet_note">Description</label>
                            <input type="text" id="wallet_note" name="description" class="form-control input-style-1" placeholder="Wallet top-up">
                        </div>
                        <button type="submit" class="j-td-btn">Save</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">Wallet statement</h4></div>
                <div class="tv-card-body table-responsive">
                    <table class="table table-responsive-sm mb-0">
                        <thead class="bg"><tr><th>Reference</th><th>Date</th><th>Description</th><th>Type</th><th>Amount</th><th>Balance</th></tr></thead>
                        <tbody>
                            @forelse($walletTransactions ?? [] as $t)
                            <tr>
                                <td><b>{{ $t->reference }}</b></td>
                                <td>{{ $t->txn_date?->format('d M Y') }}</td>
                                <td>{{ $t->description }}</td>
                                <td><span class="bullet-badge bullet-badge-{{ $t->type === 'credit' ? 'success' : 'danger' }}">{{ ucfirst($t->type) }}</span></td>
                                <td>{{ $t->type === 'credit' ? '+' : '-' }}{{ currency_symbol() }}{{ number_format($t->amount, 2) }}</td>
                                <td>{{ currency_symbol() }}{{ number_format($t->balance_after, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-muted text-center">No wallet activity yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @else
        <div class="alert alert-info mb-0">No customer found.</div>
    @endif
</div>
@endsection
