<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl'); ?>">

<?php echo $__env->make('backend.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<body class="tv-backend" dir="<?php echo e(strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr': 'rtl'); ?>">
    <script>(function(){var t=localStorage.getItem('flow-backend-theme');if(t===null){localStorage.setItem('flow-backend-theme','light');t='light';}if(t==='dark'){document.body.classList.add('dark-mode');document.body.setAttribute('data-theme','dark');document.body.setAttribute('data-theme-version','dark');}else{document.body.setAttribute('data-theme-version','light');}})();</script>
    <!--*******************  Preloader start ******************** -->
    

    <!-- Start Rtl  ==================================== -->
    

    <!--****************  Main wrapper start  *****************-->
    <div id="main-wrapper">

        <?php echo $__env->make('backend.partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php $authUser = auth()->user(); ?>
        <?php if($authUser && config('saas.enabled') && $authUser->can_access('saas_read')): ?>
            <?php echo $__env->make('backend.partials.sidebar-saas', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($authUser && !$authUser->isAdminUser() && $authUser->can_access('agent_portal_read')): ?>
            <?php echo $__env->make('backend.partials.sidebar-agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($authUser && !$authUser->isAdminUser() && $authUser->can_access('customer_portal_read')): ?>
            <?php echo $__env->make('backend.partials.sidebar-customer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($authUser && !$authUser->isAdminUser() && $authUser->can_access('staff_portal_read')): ?>
            <?php echo $__env->make('backend.partials.sidebar-staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('backend.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
        <button class="tv-sidebar-backdrop" id="backendSidebarBackdrop" type="button" aria-label="Close sidebar" tabindex="-1"></button>

        <div class="content-body">
            <?php echo $__env->yieldContent('maincontent'); ?>
        </div>

        <?php echo $__env->make('backend.partials.footer_text', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </div>
    <!--************** Main wrapper end ***************-->


    <?php echo $__env->make('backend.partials.dynamic_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!--******* Scripts ********-->
    <?php echo $__env->make('backend.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>

</html>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/partials/master.blade.php ENDPATH**/ ?>