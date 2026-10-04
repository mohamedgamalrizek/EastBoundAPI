<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Redirecting to {{ $label }}…</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('backend/css/inline-utilities.css') }}?v={{ filemtime(public_path('backend/css/inline-utilities.css')) }}">
</head>
<body class="text-center tv-payment-redirect">
    <div class="spinner-border text-primary mb-3" role="status"></div>
    <h5>Redirecting you to {{ $label }} to complete payment…</h5>
    <p class="text-muted">If you are not redirected automatically, press the button below.</p>

    <form id="gw" action="{{ $url }}" method="POST">
        @foreach($fields as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <button type="submit" class="btn btn-primary">Continue to {{ $label }}</button>
    </form>
    <script>document.getElementById('gw').submit();</script>
</body>
</html>
