{{--
    Shared frame for every generated document (invoice, receipt, voucher,
    e-ticket).

    Everything is inline CSS on purpose: dompdf renders without a browser, so
    external stylesheets, flexbox and grid are not available — tables and
    absolute widths are what survive. The agency's name, contact details and
    currency all come from Settings, so the buyer's own branding appears
    without touching a template.
--}}
@php
    $brand   = settings('name') ?: 'FLOW';
    $address = settings('address');
    $email   = settings('email');
    $phone   = settings('phone');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }} — {{ $documentNo }}</title>
    <style>
        @page { margin: 26px 30px; }
        body  { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; }

        .head       { width: 100%; border-bottom: 2px solid #111827; padding-bottom: 10px; }
        .head td    { vertical-align: top; }
        .brand-name { font-size: 19px; font-weight: bold; color: #111827; }
        .brand-meta { font-size: 10px; color: #6b7280; line-height: 1.5; }
        .doc-kicker { font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: #6b7280; }
        .doc-no     { font-size: 15px; font-weight: bold; }

        .status       { display: inline-block; padding: 3px 9px; border-radius: 9px; font-size: 9px;
                        text-transform: uppercase; letter-spacing: .5px; font-weight: bold; }
        .status-paid  { background: #dcfce7; color: #166534; }
        .status-due   { background: #fef3c7; color: #92400e; }
        .status-void  { background: #fee2e2; color: #991b1b; }
        .status-plain { background: #e5e7eb; color: #374151; }

        h2 { font-size: 12px; margin: 18px 0 6px; text-transform: uppercase; letter-spacing: .8px; color: #6b7280; }

        table.grid       { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.grid th    { background: #f3f4f6; text-align: left; padding: 7px 9px; font-size: 10px;
                           text-transform: uppercase; letter-spacing: .5px; color: #374151;
                           border-bottom: 1px solid #e5e7eb; }
        table.grid td    { padding: 7px 9px; border-bottom: 1px solid #f3f4f6; }
        table.grid .num  { text-align: right; }

        table.facts        { width: 100%; border-collapse: collapse; }
        table.facts td     { padding: 4px 0; vertical-align: top; width: 50%; }
        .label             { font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; }
        .value             { font-size: 12px; }

        .totals        { width: 46%; margin-left: 54%; margin-top: 12px; border-collapse: collapse; }
        .totals td     { padding: 5px 0; }
        .totals .num   { text-align: right; }
        .totals .grand { border-top: 2px solid #111827; font-weight: bold; font-size: 13px; }

        .note   { margin-top: 16px; padding: 9px 11px; background: #f9fafb; border-left: 3px solid #d1d5db;
                  font-size: 10px; color: #4b5563; }
        .footer { position: fixed; bottom: -6px; left: 0; right: 0; text-align: center;
                  font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>

<table class="head">
    <tr>
        <td>
            <div class="brand-name">{{ $brand }}</div>
            <div class="brand-meta">
                @if($address){{ $address }}<br>@endif
                @if($email){{ $email }}@endif
                @if($email && $phone) &middot; @endif
                @if($phone){{ $phone }}@endif
            </div>
        </td>
        <td style="text-align: right;">
            <div class="doc-kicker">{{ $documentTitle }}</div>
            <div class="doc-no">{{ $documentNo }}</div>
            @isset($documentStatus)
                <span class="status status-{{ $documentStatusTone ?? 'plain' }}">{{ $documentStatus }}</span>
            @endisset
        </td>
    </tr>
</table>

@yield('body')

<div class="footer">
    {{ $brand }} &middot; generated {{ dateTimeFormat(now()) }}
</div>

</body>
</html>
