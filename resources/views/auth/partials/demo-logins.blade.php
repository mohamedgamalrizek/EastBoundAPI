@php
    use App\Services\Demo\DemoLogins;

    $adminOnly  = $adminOnly ?? false;
    $portalOnly = $portalOnly ?? false;

    // Shown only when APP_DEMO is on AND the accounts really exist in the
    // users table — see App\Services\Demo\DemoLogins. There is no admin
    // toggle for this on purpose: a normal install never has APP_DEMO set, so
    // a buyer has nothing to switch off.
    $accounts = DemoLogins::accounts($adminOnly, $portalOnly);
@endphp

@if($accounts !== [])
    <div class="demo-logins">
        <div class="demo-logins__label">{{ ___('auth.demo_login_label') }}</div>
        <div class="demo-logins__grid">
            @foreach($accounts as $account)
                <button type="button" class="demo-chip"
                        onclick="flowDemoLogin('{{ $account['email'] }}')">{{ $account['label'] }}</button>
            @endforeach
        </div>
        <div class="demo-logins__hint">{{ ___('auth.demo_login_password_hint') }}</div>
    </div>
@endif
