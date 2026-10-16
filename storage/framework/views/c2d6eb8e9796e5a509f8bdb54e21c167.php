<?php $__env->startSection('title', 'Server Error'); ?>
<?php $__env->startSection('code', '500'); ?>
<?php $__env->startSection('tone', 'danger'); ?>
<?php $__env->startSection('glyph', '⚡'); ?>
<?php $__env->startSection('heading', 'Something Went Wrong'); ?>
<?php $__env->startSection('message', 'An unexpected error occurred on our side. The team has been notified — please try again shortly.'); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/errors/500.blade.php ENDPATH**/ ?>