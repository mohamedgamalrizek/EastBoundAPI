@php
    // Resolved once so the markup stays readable. `logo()` always returns a
    // URL — the bundled default when nothing is uploaded — so compare against
    // it to decide whether the agency really has a logo worth showing.
    $logoUrl     = logo(settings('light_theme_logo'));
    $hasRealLogo = $logoUrl !== getImage(null, 'original', 'default-image.png');

    $supportEmail = settings('email');
    $supportPhone = settings('phone');

    $expiryLine = str_replace(':minutes', $minutes, ___('mail.otp_expiry'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ ___('mail.otp_subject') }}</title>
    <style>
        /* Only progressive enhancement lives here — every critical style is
           inline, because Gmail strips <style> in some contexts. */
        @media only screen and (max-width: 600px) {
            .sp { padding-left: 24px !important; padding-right: 24px !important; }
            .code { font-size: 30px !important; letter-spacing: 8px !important; }
        }
        @media (prefers-color-scheme: dark) {
            .bg { background: #0f1420 !important; }
            .card { background: #1a2130 !important; }
            .t-strong { color: #eef2f8 !important; }
            .t-muted { color: #9aa6bb !important; }
            .code-box { background: #16233d !important; border-color: #27406b !important; }
            .code { color: #cddffb !important; }
            .rule { border-color: #27303f !important; }
        }
    </style>
</head>
<body class="bg" style="margin:0;padding:0;background:#eef1f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,Helvetica,sans-serif;color:#2b3445;">

    {{-- Inbox preview line. Hidden in the body, shown next to the subject. --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">
        {{ ___('mail.otp_intro') }}&nbsp;{{ $expiryLine }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="bg" style="background:#eef1f7;padding:40px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card" style="max-width:560px;background:#ffffff;border-radius:16px;box-shadow:0 1px 3px rgba(16,24,40,0.06);overflow:hidden;">

                    <tr>
                        <td align="center" class="sp" style="padding:36px 40px 0;">
                            @if($hasRealLogo)
                                {{-- The alt text is styled too: most clients block remote
                                     images by default, and an unreachable logo then falls
                                     back to a wordmark instead of a broken-image icon. --}}
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" height="36"
                                     style="height:36px;width:auto;border:0;outline:none;text-decoration:none;font-size:22px;font-weight:700;letter-spacing:-0.3px;color:#12345b;">
                            @else
                                {{-- No uploaded logo: a wordmark beats a broken image icon,
                                     and it still renders when a client blocks images. --}}
                                <span class="t-strong" style="font-size:22px;font-weight:700;letter-spacing:-0.3px;color:#12345b;">{{ $appName }}</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="sp t-strong" style="padding:28px 40px 0;font-size:16px;line-height:1.6;color:#2b3445;">
                            @if($name !== '')
                                <p style="margin:0 0 12px;">{{ ___('mail.otp_greeting') }} {{ $name }},</p>
                            @endif
                            <p style="margin:0;">{{ ___('mail.otp_intro') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" class="sp" style="padding:28px 40px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" class="code-box" style="background:#f2f6ff;border:1px solid #d6e2ff;border-radius:12px;padding:22px 30px;">
                                        <div class="code" style="font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace;font-size:34px;font-weight:700;letter-spacing:10px;line-height:1;color:#12345b;">{{ $code }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" class="sp t-muted" style="padding:14px 40px 0;font-size:13px;line-height:1.6;color:#6b7688;">
                            {{ $expiryLine }}
                        </td>
                    </tr>

                    <tr>
                        <td class="sp t-muted" style="padding:24px 40px 0;font-size:13px;line-height:1.6;color:#6b7688;">
                            {{ ___('mail.otp_ignore') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="sp" style="padding:28px 40px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="rule" style="border-top:1px solid #eef1f6;padding-top:18px;font-size:12px;line-height:1.7;color:#98a2b3;">
                                        <strong class="t-muted" style="color:#6b7688;">{{ $appName }}</strong>
                                        @if($supportEmail)
                                            <br><a href="mailto:{{ $supportEmail }}" style="color:#98a2b3;text-decoration:none;">{{ $supportEmail }}</a>
                                        @endif
                                        @if($supportPhone)
                                            <br>{{ $supportPhone }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
