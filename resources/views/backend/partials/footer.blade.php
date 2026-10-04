<!--********************************** Scripts ***********************************-->

{{-- Font Awesome is loaded as local CSS in partials/header.blade.php (backend/libs/fontawesome6). --}}

{{-- Core libraries are loaded individually to keep the admin shell dependency-light. --}}
<script src="{{asset('backend/libs/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{asset('backend/libs/bootstrap4/js/bootstrap.bundle.min.js') }}"></script>

<!-- Vectormap -->
{{-- <script src="{{asset('backend/libs/jqvmap/js/jquery.vmap.min.js') }}"></script>
<script src="{{asset('backend/libs/jqvmap/js/jquery.vmap.world.js') }}"></script> --}}

<!--  flot-chart js -->
{{-- <script src="{{asset('backend/libs/flot/jquery.flot.js') }}"></script>
<script src="{{asset('backend/libs/flot/jquery.flot.resize.js') }}"></script> --}}

<!-- Chart Chartist plugin files -->
{{-- <script src="{{asset('backend/libs/chartist/js/chartist.min.js') }}"></script>
<script src="{{asset('backend/libs/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js') }}"></script>
<script src="{{asset('backend/js/plugins-init/chartist-init.js') }}"></script> --}}


<!-- Owl Carousel -->
{{-- <script src="{{asset('backend/libs/owl-carousel/js/owl.carousel.min.js') }}"></script> --}}

<!-- Counter Up -->
{{-- <script src="{{asset('backend/libs/waypoints/jquery.waypoints.min.js') }}"></script>
<script src="{{asset('backend/libs/jquery.counterup/jquery.counterup.min.js') }}"></script> --}}

{{-- <script src="{{asset('backend/js/dashboard/dashboard-1.js') }}"></script> --}}

{{-- sweetalert2 --}}
<script src="{{asset('backend/libs/sweetalert2/js/sweetalert2.all.min.js') }}"></script>

<!-- select 2 js -->
<script src="{{asset('backend/libs/select2-4.1/js/select2.min.js') }}"></script>

{{-- flatpickr --}}
<script src="{{asset('backend/libs/flatpickr/flatpickr.min.js') }}"></script>

{{-- DataTables (shared across all panels) --}}
<script src="{{ asset('backend/libs/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/libs/datatables/js/dataTables.bootstrap4.min.js') }}"></script>

<script src="{{ asset('backend/js/custom/dynamic_modal.js') }}"></script>

<script src="{{ asset('backend/js/custom/_developer.js') }}?v={{ filemtime(public_path('backend/js/custom/_developer.js')) }}"></script>

{{-- Delete confirmation. Loaded globally (and cache-busted) because every
     listing relies on tryDelete(): if this script is missing, the inline
     onclick throws and the browser follows the href, turning the DELETE
     route into a GET and showing a MethodNotAllowed error page. --}}
<script src="{{ asset('backend/js/custom/delete_ajax.js') }}?v={{ filemtime(public_path('backend/js/custom/delete_ajax.js')) }}"></script>

{{-- FLOW admin behaviour layer (collapsible filters, etc.) --}}
<script src="{{ asset('backend/js/flow-admin.js') }}"></script>

{{-- ApexCharts is only needed on dashboards/reports that render #flow-data. --}}
@if(request()->is('dashboard', 'accounting/dashboard', 'visa/dashboard', 'visa/reports', 'flight/reports', 'reports-center/*', 'portal/agent/reports'))
    <script src="{{ asset('backend/libs/apexcharts/apexcharts.min.js') }}"></script>
@endif

{{-- Consolidated page behaviours (charts, todo, etc.) --}}
<script src="{{ asset('backend/js/custom/flow-app.js') }}"></script>

{{-- Consolidated inline script extracts (DataTable, select2, resendToken, etc.) --}}
<script src="{{ asset('backend/js/custom/flow-inline.js') }}?v={{ filemtime(public_path('backend/js/custom/flow-inline.js')) }}"></script>

{{-- Dark mode toggle handler --}}
<script src="{{ asset('backend/js/custom/dark-mode.js') }}"></script>

@include('backend.partials.alert-message')
