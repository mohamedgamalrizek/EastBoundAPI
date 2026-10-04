{{-- Shared error page shell. Child views supply: code, heading, message,
     glyph and tone (danger | warning | info).
     Kept self-contained on purpose — an error page must still render when the
     app's own assets or helpers are the thing that broke. --}}
@php
    $tone = trim($__env->yieldContent('tone')) ?: 'danger';
    $tone = in_array($tone, ['danger', 'warning', 'info'], true) ? $tone : 'danger';

    // Send signed-in users back to the panel their role can actually open,
    // otherwise a 403 "Back to Home" just bounces them into another 403.
    try {
        $homeUrl = auth()->check() && method_exists(auth()->user(), 'home')
            ? auth()->user()->home()
            : url('/');
    } catch (\Throwable $e) {
        $homeUrl = url('/');
    }

    try { $iconHref = favicon(); } catch (\Throwable $e) { $iconHref = null; }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="tone-{{ $tone }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name', 'FLOW') }}</title>
    @if($iconHref)
        <link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="{{ $iconHref }}">
    @endif

    {{-- No filemtime() cache-bust here on purpose: this page must still
         render if a filesystem read is what's failing. asset() only builds
         a URL string, so it can't throw. --}}
    <link rel="stylesheet" href="{{ asset('errors/css/layout.css') }}">
</head>

<body>
    <main class="err-card" role="alert">
        <div class="err-icon" aria-hidden="true">@yield('glyph', '!')</div>

        <h1 class="err-code">@yield('code')</h1>
        <h2 class="err-heading">@yield('heading')</h2>
        <p class="err-message">@yield('message')</p>

        <div class="err-actions">
            <a class="err-btn err-btn-primary" href="{{ $homeUrl }}">Back to Home</a>
            <button type="button" class="err-btn err-btn-ghost" onclick="history.back()">Go Back</button>
        </div>

        @hasSection('extra')
            <div class="err-foot">@yield('extra')</div>
        @endif
    </main>
</body>

</html>
