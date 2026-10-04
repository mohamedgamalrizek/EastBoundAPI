<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl' }}">

@include('backend.partials.header')

{{-- <body class="tv-backend" dir="rtl"> --}}
<body class="tv-backend" dir="{{ strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr': 'rtl' }}">
    <script>(function(){var t=localStorage.getItem('flow-backend-theme');if(t===null){localStorage.setItem('flow-backend-theme','light');t='light';}if(t==='dark'){document.body.classList.add('dark-mode');document.body.setAttribute('data-theme','dark');document.body.setAttribute('data-theme-version','dark');}else{document.body.setAttribute('data-theme-version','light');}})();</script>
    <!--*******************  Preloader start ******************** -->
    {{-- <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div> --}}

    <!-- Start Rtl  ==================================== -->
    {{-- <button type="button" class="rtl-mode">RTL/LTL</button> --}}

    <!--****************  Main wrapper start  *****************-->
    <div id="main-wrapper">

        @include('backend.partials.navbar')

        @php $authUser = auth()->user(); @endphp
        @if($authUser && config('saas.enabled') && $authUser->can_access('saas_read'))
            @include('backend.partials.sidebar-saas')
        @elseif($authUser && !$authUser->isAdminUser() && $authUser->can_access('agent_portal_read'))
            @include('backend.partials.sidebar-agent')
        @elseif($authUser && !$authUser->isAdminUser() && $authUser->can_access('customer_portal_read'))
            @include('backend.partials.sidebar-customer')
        @elseif($authUser && !$authUser->isAdminUser() && $authUser->can_access('staff_portal_read'))
            @include('backend.partials.sidebar-staff')
        @else
            @include('backend.partials.sidebar')
        @endif
        <button class="tv-sidebar-backdrop" id="backendSidebarBackdrop" type="button" aria-label="Close sidebar" tabindex="-1"></button>

        <div class="content-body">
            @yield('maincontent')
        </div>

        @include('backend.partials.footer_text')

    </div>
    <!--************** Main wrapper end ***************-->


    @include('backend.partials.dynamic_modal')

    <!--******* Scripts ********-->
    @include('backend.partials.footer')

    @stack('scripts')

</body>

</html>
