<?php $__env->startSection('title'); ?> <?php echo e(___('menus.login')); ?> <?php $__env->stopSection(); ?>
<?php $__env->startSection('brand_name'); ?><?php echo e(___('auth.admin_brand_name')); ?><?php $__env->stopSection(); ?>
<?php $__env->startSection('brand_features'); ?><?php echo e(___('auth.admin_brand_features')); ?><?php $__env->stopSection(); ?>
<?php $__env->startSection('brand_quote_stars'); ?><?php echo e(___('auth.admin_access_label')); ?><?php $__env->stopSection(); ?>
<?php $__env->startSection('brand_quote_text'); ?><?php echo e(___('auth.admin_invitation_notice')); ?><?php $__env->stopSection(); ?>
<?php $__env->startSection('brand_quote_name'); ?><?php echo e(___('auth.admin_account_owner')); ?><?php $__env->stopSection(); ?>
<?php $__env->startSection('brand_quote_role'); ?><?php echo e(___('auth.admin_account_policy')); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('brand_heading'); ?>
    <?php
        $heading = ___('auth.admin_brand_heading');
    ?>
    <h1><?php echo nl2br(e($heading)); ?></h1>
    <p class="sub"><?php echo e(___('auth.admin_brand_subtitle')); ?></p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('form'); ?>
    <h2><?php echo e(___('auth.admin_form_title')); ?></h2>
    <p class="lead-sm"><?php echo e(___('auth.admin_form_intro')); ?></p>

    <form action="<?php echo e(route('admin.login')); ?>" method="POST" data-demo>
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label"><?php echo e(___('label.Email')); ?></label>
            <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>"
                   placeholder="<?php echo e(___('placeholder.enter_email')); ?>" autofocus>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
            <div class="row-between mb-1">
                <label class="form-label mb-0"><?php echo e(___('label.Password')); ?></label>
                <a class="forgot" href="<?php echo e(route('password.forgotForm')); ?>"><?php echo e(___('label.Forgot Password')); ?>?</a>
            </div>
            <div class="input-wrap">
                <input type="password" name="password" id="apwd" class="form-control"
                       placeholder="<?php echo e(___('placeholder.enter_password')); ?>">
                <span class="toggle" onclick="var p=document.getElementById('apwd');p.type=p.type==='password'?'text':'password';this.textContent=p.type==='password'?'<?php echo e(___('frontend.show')); ?>':'<?php echo e(___('frontend.hide')); ?>';"><?php echo e(___('frontend.show')); ?></span>
            </div>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="row-between mb-4 mt-3">
            <label class="remember mb-0">
                <input type="checkbox" name="remember" <?php if(old('remember')): echo 'checked'; endif; ?> class="mr-1">
                <?php echo e(___('label.Remember Me')); ?>

            </label>
        </div>

        <button type="submit" class="btn-blue"><?php echo e(___('auth.admin_submit_button')); ?></button>
    </form>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('auth.saas-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/auth/admin-login.blade.php ENDPATH**/ ?>