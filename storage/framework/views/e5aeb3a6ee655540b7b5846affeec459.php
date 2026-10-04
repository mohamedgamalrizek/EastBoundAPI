<?php if(Session::has('success')): ?>
<div class="session-alert" data-message="<?php echo e(Session::get('success')); ?>" data-icon="success"></div>
<?php endif; ?>
<?php if(Session::has('danger')): ?>
<div class="session-alert" data-message="<?php echo e(Session::get('danger')); ?>" data-icon="error"></div>
<?php endif; ?>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/partials/alert-message.blade.php ENDPATH**/ ?>