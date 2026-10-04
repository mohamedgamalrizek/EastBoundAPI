<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment {{ ucfirst($payment->status) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('saas/css/payment-result.css') }}?v={{ filemtime(public_path('saas/css/payment-result.css')) }}" rel="stylesheet">
</head>
<body>
<div class="wrap">
    @php
        $map = ['success'=>['ic-success','✓','Payment successful'],'failed'=>['ic-failed','✕','Payment failed'],'cancelled'=>['ic-cancelled','!','Payment cancelled'],'pending'=>['ic-pending','…','Payment pending']];
        [$colorClass,$icon,$title] = $map[$payment->status] ?? $map['pending'];
    @endphp
    <div class="card shadow-sm border-0"><div class="card-body p-5 text-center">
        <div class="ic {{ $colorClass }}">{{ $icon }}</div>
        <h4>{{ $title }}</h4>
        @if(session('info'))<div class="alert alert-info mt-3">{{ session('info') }}</div>@endif
        @if(session('danger'))<div class="alert alert-danger mt-3">{{ session('danger') }}</div>@endif

        <div class="text-left mt-4 kv-box">
            <div class="d-flex justify-content-between py-1"><span class="text-muted">Reference</span><b>{{ $payment->reference }}</b></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted">Gateway</span><b>{{ ucfirst($payment->gateway) }}</b></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted">Amount</span><b>{{ $payment->currency }} {{ number_format($payment->amount) }}</b></div>
            @if($payment->gateway_ref)<div class="d-flex justify-content-between py-1"><span class="text-muted">Txn ID</span><b>{{ $payment->gateway_ref }}</b></div>@endif
        </div>
        <a href="{{ route('saas.signup') }}" class="btn btn-outline-secondary mt-4">Back</a>
    </div></div>
</div>
</body>
</html>
