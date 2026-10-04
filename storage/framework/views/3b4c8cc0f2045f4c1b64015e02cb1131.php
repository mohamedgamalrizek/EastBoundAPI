<!--********************************** Scripts ***********************************-->




<script src="<?php echo e(asset('backend/libs/jquery/jquery-3.7.1.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/libs/bootstrap4/js/bootstrap.bundle.min.js')); ?>"></script>

<!-- Vectormap -->


<!--  flot-chart js -->


<!-- Chart Chartist plugin files -->



<!-- Owl Carousel -->


<!-- Counter Up -->





<script src="<?php echo e(asset('backend/libs/sweetalert2/js/sweetalert2.all.min.js')); ?>"></script>

<!-- select 2 js -->
<script src="<?php echo e(asset('backend/libs/select2-4.1/js/select2.min.js')); ?>"></script>


<script src="<?php echo e(asset('backend/libs/flatpickr/flatpickr.min.js')); ?>"></script>


<script src="<?php echo e(asset('backend/libs/datatables/js/jquery.dataTables.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/libs/datatables/js/dataTables.bootstrap4.min.js')); ?>"></script>

<script src="<?php echo e(asset('backend/js/custom/dynamic_modal.js')); ?>"></script>

<script src="<?php echo e(asset('backend/js/custom/_developer.js')); ?>?v=<?php echo e(filemtime(public_path('backend/js/custom/_developer.js'))); ?>"></script>


<script src="<?php echo e(asset('backend/js/custom/delete_ajax.js')); ?>?v=<?php echo e(filemtime(public_path('backend/js/custom/delete_ajax.js'))); ?>"></script>


<script src="<?php echo e(asset('backend/js/flow-admin.js')); ?>"></script>


<?php if(request()->is('dashboard', 'accounting/dashboard', 'visa/dashboard', 'visa/reports', 'flight/reports', 'reports-center/*', 'portal/agent/reports')): ?>
    <script src="<?php echo e(asset('backend/libs/apexcharts/apexcharts.min.js')); ?>"></script>
<?php endif; ?>


<script src="<?php echo e(asset('backend/js/custom/flow-app.js')); ?>"></script>


<script src="<?php echo e(asset('backend/js/custom/flow-inline.js')); ?>?v=<?php echo e(filemtime(public_path('backend/js/custom/flow-inline.js'))); ?>"></script>


<script src="<?php echo e(asset('backend/js/custom/dark-mode.js')); ?>"></script>

<?php echo $__env->make('backend.partials.alert-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/partials/footer.blade.php ENDPATH**/ ?>