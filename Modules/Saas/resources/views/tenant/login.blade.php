<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ $tenantName ?? 'FLOW' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('saas/css/tenant-login.css') }}?v={{ filemtime(public_path('saas/css/tenant-login.css')) }}" rel="stylesheet">
</head>
<body>
<div class="login-card">
    <div class="text-center mb-3">
        <h3 class="brand mb-0">FLOW<span class="brand-dot">.</span></h3>
        <div class="text-secondary">{{ $tenantName ?? 'Workspace' }}</div>
    </div>
    <div class="card shadow border-0"><div class="card-body p-4">
        @if(session('danger'))<div class="alert alert-danger py-2">{{ session('danger') }}</div>@endif
        <form action="{{ route('tenant.login.attempt') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" autofocus>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <button type="submit" class="btn btn-blue btn-block">Sign in</button>
        </form>
    </div></div>
    <p class="text-center text-secondary mt-3 fs-sm">Your isolated workspace · powered by FLOW</p>
</div>
</body>
</html>
