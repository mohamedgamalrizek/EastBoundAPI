<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl' }}" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>@yield('title') </title>

    <!-- Favicon icon -->
    <link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="{{ favicon(settings('favicon')) }}">

    <link href="{{asset('backend')}}/vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/chartist/css/chartist.min.css">
    <link href="{{ asset('backend/css/backend-foundation.css') }}?v={{ filemtime(public_path('backend/css/backend-foundation.css')) }}" rel="stylesheet">
    <link href="{{asset('backend')}}/css/custom.css" rel="stylesheet">
    <link href="{{ asset('backend/css/backend-responsive.css') }}?v={{ filemtime(public_path('backend/css/backend-responsive.css')) }}" rel="stylesheet">


    {{-- select 2 & flatfikr for date css  --}}
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/select2/css/select2.min.css" />
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/flatpickr/flatpickr.min.css">

    @stack('styles')

</head>

<body class="h-100 auth-body" dir="{{ strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr': 'rtl' }}">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-4">
                    <div class="authincation-content">
                        @yield('main')
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="{{asset('backend')}}/vendor/jquery/jquery.min.js"></script>
    <script src="{{asset('backend')}}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>


    <!-- select 2 js -->
    <script src="{{asset('backend')}}/vendor/select2/js/select2.full.min.js"></script>
    {{-- flatpickr --}}
    <script src="{{asset('backend')}}/vendor/flatpickr/flatpickr.min.js"></script>


    @include('backend.partials.alert-message')

    {{-- Consolidated inline script extracts (select2, flatpickr, etc.) --}}
    <script src="{{ asset('backend/js/custom/flow-inline.js') }}?v={{ filemtime(public_path('backend/js/custom/flow-inline.js')) }}"></script>

    @stack('scripts')

</body>

</html>
