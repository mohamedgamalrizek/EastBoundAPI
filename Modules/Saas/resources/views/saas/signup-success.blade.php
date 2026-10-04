<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Workspace ready — {{ config('saas-landing.brand') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('saas/css/signup-success.css') }}?v={{ filemtime(public_path('saas/css/signup-success.css')) }}" rel="stylesheet">
</head>
<body>
<div class="wrap">
    <div class="card shadow-sm border-0"><div class="card-body p-5 text-center">
        <div class="ok">✓</div>
        <h3 class="mb-1">Welcome aboard, {{ $company }}!</h3>
        <p class="text-muted">Your isolated workspace and database are ready.</p>

        <div class="kv text-left my-4">
            <div class="d-flex justify-content-between py-1"><span class="text-muted">Workspace</span> <b>{{ $domain }}</b></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted">Admin email</span> <b>{{ $email }}</b></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted">Plan</span> <b>{{ $plan->name }} — ৳{{ number_format($plan->price) }}/{{ $plan->billing_cycle }}</b></div>
        </div>

        <p class="text-muted fs-85">
            Sign in at <b>http://{{ $domain }}/workspace</b> with the email and password you just set.
            (On a live server this needs the wildcard subdomain pointed at the app.)
        </p>
        <a href="{{ route('saas.signup') }}" class="btn btn-blue mt-2">Create another workspace</a>
    </div></div>
    <p class="text-center brand mt-3">{{ config('saas-landing.brand') }}<span class="brand-dot">.</span></p>
</div>
</body>
</html>
