<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>Set up FLOW</title>
    <link rel="icon" type="image/png" href="{{ asset('backend/assets/img/logo/flow-favicon.png') }}">
    <link href="{{ asset('installer/css/wizard.css') }}?v={{ filemtime(public_path('installer/css/wizard.css')) }}" rel="stylesheet">
</head>
<body>
    <main class="shell">
        <aside class="aside" aria-label="FLOW installation overview">
            <div class="brand"><img class="brand-logo" src="{{ asset('backend/assets/img/logo/flow-logo-white.png') }}" alt="FLOW"></div>
            <h1>Set up your travel operations workspace.</h1>
            <p>Connect a fresh database and create the first administrator.</p>
            <ol class="steps">
                <li class="step"><span class="step-index">1</span><div><strong>System ready</strong><span>Server and file checks are complete.</span></div></li>
                <li class="step"><span class="step-index">2</span><div><strong>Configure workspace</strong><span>Add application, database, and admin details.</span></div></li>
                <li class="step"><span class="step-index">3</span><div><strong>Review and install</strong><span>Confirm settings before any tables are created.</span></div></li>
            </ol>
            <div class="aside-foot">FLOW installs only into an empty database. Existing data is never deleted.</div>
        </aside>

        <section class="panel" aria-labelledby="installer-title">
            <p class="eyebrow">New deployment</p>
            <h2 id="installer-title">Configure FLOW</h2>
            <p class="intro">Provide the details for this deployment. You can review non-secret settings before installation starts.</p>

            @if ($errors->any())
                <div class="alert" role="alert">
                    <div><strong>Review the highlighted setup details.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                </div>
            @endif

            <details class="requirements" {{ ! $passes ? 'open' : '' }}>
                <summary><span>Server requirements</span><small>{{ $passes ? 'All checks passed' : 'Action required' }}</small></summary>
                <div class="checks">@foreach ($checks as $check)<span class="check {{ $check['passed'] ? 'good' : 'bad' }}">{{ $check['passed'] ? '✓' : '×' }} {{ $check['label'] }}</span>@endforeach</div>
            </details>

            <form method="post" action="{{ route('installer.store') }}">
                @csrf
                <fieldset class="section">
                    <div class="section-header"><h3>Application</h3><p>Public address of the site.</p></div>
                    <div class="fields">
                        <div class="field"><label for="app_name">Application name</label><input id="app_name" name="app_name" value="{{ old('app_name', 'FLOW') }}" required><span class="helper" aria-hidden="true"></span></div>
                        <div class="field"><label for="app_url">Application URL</label><input id="app_url" type="url" name="app_url" value="{{ old('app_url', url('/')) }}" required><span class="helper" aria-hidden="true"></span></div>
                        <div class="field"><label for="timezone">Timezone</label><input id="timezone" name="timezone" value="{{ old('timezone', 'Asia/Dhaka') }}" required><span class="helper" aria-hidden="true"></span></div>
                    </div>
                </fieldset>

                <fieldset class="section">
                    <div class="section-header"><h3>Database</h3><p>MySQL or MariaDB. Create the database first.</p></div>
                    <div class="fields">
                        <div class="field"><label for="db_host">Host</label><input id="db_host" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required><span class="helper" aria-hidden="true"></span></div>
                        <div class="field"><label for="db_port">Port</label><input id="db_port" type="number" name="db_port" value="{{ old('db_port', 3306) }}" required><span class="helper" aria-hidden="true"></span></div>
                        <div class="field"><label for="db_database">Database name</label><input id="db_database" name="db_database" value="{{ old('db_database') }}" required><p class="helper">Must exist and contain no tables.</p></div>
                        <div class="field"><label for="db_username">Username</label><input id="db_username" name="db_username" value="{{ old('db_username') }}" required><span class="helper" aria-hidden="true"></span></div>
                        <div class="field full"><label for="db_password">Password</label><input id="db_password" type="password" name="db_password" autocomplete="new-password"><p class="helper">Credentials are used only to connect and are never placed in a URL.</p></div>
                    </div>
                </fieldset>

                <fieldset class="section">
                    <div class="section-header"><h3>Administrator</h3><p>Create the first full-access account.</p></div>
                    <div class="fields">
                        <div class="field"><label for="admin_name">Full name</label><input id="admin_name" name="admin_name" value="{{ old('admin_name') }}" autocomplete="name" required><span class="helper" aria-hidden="true"></span></div>
                        <div class="field"><label for="admin_email">Email address</label><input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}" autocomplete="email" required><span class="helper" aria-hidden="true"></span></div>
                        <div class="field"><label for="admin_phone">Phone (optional)</label><input id="admin_phone" type="tel" name="admin_phone" value="{{ old('admin_phone') }}" autocomplete="tel"><span class="helper" aria-hidden="true"></span></div>
                        <div class="field"><label for="admin_password">Password</label><input id="admin_password" type="password" name="admin_password" autocomplete="new-password" minlength="8" required><p class="helper">Use at least 8 characters.</p></div>
                        <div class="field"><label for="admin_password_confirmation">Confirm password</label><input id="admin_password_confirmation" type="password" name="admin_password_confirmation" autocomplete="new-password" minlength="8" required><span class="helper" aria-hidden="true"></span></div>
                    </div>
                </fieldset>

                <div class="actions"><small>Your settings are validated before installation begins.</small><button type="submit" @disabled(! $passes)>Review installation</button></div>
            </form>
        </section>
    </main>
</body>
</html>
