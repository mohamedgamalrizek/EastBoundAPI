<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'FLOW') — {{ ___('frontend.travel_agency_suffix') }}</title>
    <meta name="description" content="@yield('meta', ___('frontend.app_layout_meta_description'))">

    <link href="{{ asset('fonts/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/libs/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/libs/fontawesome6/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body data-page="@yield('page', '')">

    @include('frontend.partials.header')

    @yield('content')

    @include('frontend.partials.footer')

    <script src="{{ asset('backend/libs/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('backend/libs/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
