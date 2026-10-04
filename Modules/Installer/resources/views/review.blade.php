<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>Review FLOW setup</title>
    <link rel="icon" type="image/png" href="{{ asset('backend/assets/img/logo/flow-favicon.png') }}">
    <link href="{{ asset('installer/css/review.css') }}?v={{ filemtime(public_path('installer/css/review.css')) }}" rel="stylesheet">
</head>
<body>
    <main class="page">
        <header class="topbar">
            <div class="brand"><img class="brand-logo" src="{{ asset('backend/assets/img/logo/flow-logo.png') }}" alt="FLOW"></div>
            <div class="status">Installation runs only on an empty database</div>
        </header>

        <nav class="steps" aria-label="Installation progress">
            <div class="step done"><span class="step-number">1</span><span>System check</span></div>
            <div class="step done"><span class="step-number">2</span><span>Configuration</span></div>
            <div class="step current"><span class="step-number">3</span><span>Review</span></div>
        </nav>

        <section class="panel" aria-labelledby="review-title">
            <div class="panel-head">
                <p class="eyebrow">Final confirmation</p>
                <h1 id="review-title">Ready to install</h1>
                <p class="intro">Review the non-secret settings below. The installer will create the schema, seed required data, and create the first administrator account.</p>
            </div>

            <div class="panel-body">
                @if ($errors->any())
                    <div class="alert" role="alert">
                        <strong>Installation could not start.</strong>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <dl class="summary">
                    <div class="summary-item">
                        <dt>Application</dt>
                        <dd>{{ $data['app_name'] }}<br><span>{{ $data['app_url'] }}</span></dd>
                    </div>
                    <div class="summary-item">
                        <dt>Database</dt>
                        <dd>{{ $data['db_username'] }} <span>at</span> {{ $data['db_host'] }}:{{ $data['db_port'] }}/{{ $data['db_database'] }}</dd>
                    </div>
                    <div class="summary-item">
                        <dt>Administrator</dt>
                        <dd>{{ $data['admin_name'] }}<br><span>{{ $data['admin_email'] }}</span></dd>
                    </div>
                </dl>

                <div class="notice">
                    <span class="notice-dot" aria-hidden="true"></span>
                    <span>Keep this browser tab open while FLOW runs migrations and prepares the workspace.</span>
                </div>

                <div class="actions">
                    <a class="back" href="{{ route('installer.show') }}">Back to setup</a>
                    <form method="post" action="{{ route('installer.run') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <button type="submit">Run secure installation</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
