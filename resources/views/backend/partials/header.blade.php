<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="viewport" content="width=device-width, minimum-scale=0.8, maximum-scale = 0.8, user-scalable = no , shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title> @yield('title') </title>

    <!-- Favicon icon -->
    <link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="{{ favicon(settings('favicon')) }}">

    {{-- Fonts (self-hosted in public/fonts — the app makes no external font requests) --}}
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}" />

    {{-- Font Awesome 6 Free (bundled locally; v4-shims keeps the older `fa fa-*` markup working) --}}
    <link rel="stylesheet" href="{{ asset('backend/libs/fontawesome6/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/libs/fontawesome6/css/v4-shims.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/css/bootstrap.css') }}" />
    <link rel="stylesheet" href="{{asset('backend/css/custom.css')}}" />
    {{-- <link rel="stylesheet" href="{{ asset('backend/css/backend-responsive.css') }}?v={{ filemtime(public_path('backend/css/backend-responsive.css')) }}" /> --}}


    {{-- select 2 & flatfikr for date css  --}}
    <link rel="stylesheet" href="{{asset('backend/libs/select2-4.1/css/select2.min.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/libs/flatpickr/flatpickr.min.css')}}">

    {{-- DataTables (shared across all panels) --}}
    <link rel="stylesheet" href="{{ asset('backend/libs/datatables/css/dataTables.bootstrap4.min.css') }}" />

    {{-- 🔹 FLOW admin re-skin (blue theme) — must load last to override the template --}}
    <link rel="stylesheet" href="{{ asset('backend/css/sass/main.css') }}?v={{ filemtime(public_path('backend/css/sass/main.css')) }}" />
    <link rel="stylesheet" href="{{ asset('backend/css/inline-utilities.css') }}?v={{ filemtime(public_path('backend/css/inline-utilities.css')) }}" />

    {{-- Dark mode overrides — activated via .dark-mode class on body --}}
    {{-- <link rel="stylesheet" href="{{ asset('backend/css/dark-mode.css') }} " /> --}}

    @stack('styles')

</head>
