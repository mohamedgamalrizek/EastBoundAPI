<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Workspace') · {{ $tenantName ?? 'FLOW' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('saas/css/tenant-layout.css') }}?v={{ filemtime(public_path('saas/css/tenant-layout.css')) }}" rel="stylesheet">
</head>
<body>
    <div class="t-top">
        <div class="brand">FLOW<span class="brand-dot">.</span> <small class="text-muted ml-2 fw-500">{{ $tenantName ?? 'Workspace' }}</small></div>
        <div>
            <span class="text-muted mr-3 fs-90">{{ session('tenant_user_name') }} <span class="badge-soft">{{ session('tenant_user_role') }}</span></span>
            <form action="{{ route('tenant.logout') }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-sm btn-outline-secondary">Logout</button>
            </form>
        </div>
    </div>
    <div class="t-wrap">
        <nav class="t-side">
            <a href="{{ route('tenant.dashboard') }}" class="{{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">📊 Dashboard</a>
            <a href="{{ route('tenant.customers') }}" class="{{ request()->routeIs('tenant.customers') ? 'active' : '' }}">👥 Customers</a>
            <a href="{{ route('tenant.packages') }}" class="{{ request()->routeIs('tenant.packages') ? 'active' : '' }}">🎫 Packages</a>
            <a href="{{ route('tenant.bookings') }}" class="{{ request()->routeIs('tenant.bookings') ? 'active' : '' }}">🧾 Bookings</a>
        </nav>
        <main class="t-main">
            @if(session('danger'))<div class="alert alert-danger">{{ session('danger') }}</div>@endif
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @yield('content')
        </main>
    </div>
</body>
</html>
