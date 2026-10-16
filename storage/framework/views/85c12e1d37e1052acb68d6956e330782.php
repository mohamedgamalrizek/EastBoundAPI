<?php
    use App\Services\Demo\DemoLogins;

    $adminOnly  = $adminOnly ?? false;
    $portalOnly = $portalOnly ?? false;

    // Shown only when APP_DEMO is on AND the accounts really exist in the
    // users table — see App\Services\Demo\DemoLogins. There is no admin
    // toggle for this on purpose: a normal install never has APP_DEMO set, so
    // a buyer has nothing to switch off.
    $accounts = DemoLogins::accounts($adminOnly, $portalOnly);
?>

<?php if($accounts !== []): ?>
    <div class="demo-logins">
        <div class="demo-logins__label"><?php echo e(___('auth.demo_login_label')); ?></div>
        <div class="demo-logins__grid">
            <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" class="demo-chip"
                        onclick="flowDemoLogin('<?php echo e($account['email']); ?>')"><?php echo e($account['label']); ?></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="demo-logins__hint"><?php echo e(___('auth.demo_login_password_hint')); ?></div>
    </div>
<?php endif; ?>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/auth/partials/demo-logins.blade.php ENDPATH**/ ?>