<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>FLOW is ready</title>
    <link rel="icon" type="image/png" href="{{ asset('backend/assets/img/logo/flow-favicon.png') }}">
    <link href="{{ asset('installer/css/complete.css') }}?v={{ filemtime(public_path('installer/css/complete.css')) }}" rel="stylesheet">
</head>
<body>
    <main class="page">
        <header class="topbar">
            <div class="brand"><img class="brand-logo" src="{{ asset('backend/assets/img/logo/flow-logo.png') }}" alt="FLOW"></div>
            <div class="status">Workspace setup complete</div>
        </header>

        <nav class="steps" aria-label="Installation progress">
            <div class="step done"><span class="step-number">1</span><span>System check</span></div>
            <div class="step done"><span class="step-number">2</span><span>Configuration</span></div>
            <div class="step current"><span class="step-number">3</span><span>Installed</span></div>
        </nav>

        <section class="panel" aria-labelledby="complete-title">
            <div class="panel-head">
                <p class="eyebrow">Installation complete</p>
                <h1 id="complete-title">FLOW is ready</h1>
                <p class="intro">Your database, base configuration, and first administrator account have been created.</p>
            </div>

            <div class="panel-body">
                <div class="success">
                    <span class="success-icon" aria-hidden="true"></span>
                    <div>
                        <b>Setup completed successfully</b>
                        <span>Sign in with the administrator email and password you chose during setup.</span>
                    </div>
                </div>

                <div class="actions">
                    <a class="cta" href="{{ route('admin.loginForm') }}">Open administrator sign in</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
