<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Start your {{ config('saas-landing.brand') }} workspace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('saas/css/signup.css') }}?v={{ filemtime(public_path('saas/css/signup.css')) }}" rel="stylesheet">
</head>
<body>
<div class="signup-card">
    <div class="text-center mb-4">
        <h2 class="brand mb-1">{{ config('saas-landing.brand') }}<span class="brand-dot">.</span></h2>
        <p class="text-muted mb-0">Spin up your own travel-agency workspace in seconds.</p>
    </div>

    @if(session('danger'))
        <div class="alert alert-danger">{{ session('danger') }}</div>
    @endif

    <form action="{{ route('saas.signup.store') }}" method="POST" class="card shadow-sm border-0">
        @csrf
        <div class="card-body p-4">

            <h5 class="mb-3">1. Choose a plan</h5>
            <div class="row mb-4">
                @foreach($plans as $i => $plan)
                    <div class="col-md-4 mb-3">
                        <label class="mb-0 w-100 plan-label">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" class="plan-radio" @checked(old('plan_id', $loop->first ? $plan->id : null) == $plan->id)>
                            <div class="plan-card">
                                <div class="font-weight-bold">{{ $plan->name }}</div>
                                <div class="plan-price">৳{{ number_format($plan->price) }}<small class="text-muted fs-sm">/{{ $plan->billing_cycle }}</small></div>
                                <div class="text-muted mb-2 fs-sm">{{ $plan->max_users ? $plan->max_users.' users' : 'Unlimited users' }}</div>
                                @foreach(collect($plan->features)->take(4) as $f)
                                    <div class="feat">✓ {{ $f }}</div>
                                @endforeach
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('plan_id') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror

            <h5 class="mb-3">2. Your workspace</h5>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Company name</label>
                    <input type="text" name="company" class="form-control" value="{{ old('company') }}" placeholder="Skyline Travels">
                    @error('company') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Workspace address</label>
                    <div class="input-group">
                        <input type="text" name="subdomain" class="form-control" value="{{ old('subdomain') }}" placeholder="skyline">
                        <div class="input-group-append"><span class="sub-suffix">.{{ $domainBase }}</span></div>
                    </div>
                    @error('subdomain') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Admin email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@company.com">
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control">
                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-3">
                    <label>Confirm</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-blue btn-lg btn-block mt-2">Create my workspace →</button>
            <p class="text-center text-muted mt-2 fs-82">A dedicated, isolated database is provisioned for your company automatically.</p>
        </div>
    </form>
</div>
</body>
</html>
