<?php
    $appName = settings('name') ?: 'FLOW';
    $authBrandName = ___('auth.brand_name');

    // The brand panel is shared by login, registration and the staff sign-in,
    // but they are not selling the same thing: registration creates a customer
    // account, so the agency pitch and the agency-owner testimonial belong on
    // the other two. A page overrides what it needs and inherits the rest.
    // Sections are already collected by the time this layout renders.
    $authFeaturesRaw = trim($__env->yieldContent('brand_features')) ?: ___('auth.brand_features');
    $authQuoteText   = trim($__env->yieldContent('brand_quote_text')) ?: ___('auth.quote_text');
    $authQuoteName   = trim($__env->yieldContent('brand_quote_name')) ?: ___('auth.quote_name');
    $authQuoteRole   = trim($__env->yieldContent('brand_quote_role')) ?: ___('auth.quote_role');

    $authFeatures = collect(preg_split('/\r\n|\r|\n/', (string) $authFeaturesRaw))
        ->map(fn ($feature) => trim($feature))
        ->filter()
        ->values();
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl'); ?>" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title'); ?> - <?php echo e($appName); ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo e(favicon(settings('favicon'))); ?>">
    <link href="<?php echo e(asset('backend/libs/bootstrap4/css/bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('backend/libs/fontawesome6/css/all.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('auth/css/saas-layout.css')); ?>?v=<?php echo e(filemtime(public_path('auth/css/saas-layout.css'))); ?>" rel="stylesheet">
    <?php echo $__env->yieldPushContent('styles'); ?>
    <style>
        .auth-flash { border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; font-size: 14px; line-height: 1.5; }
        .auth-flash--ok   { background: #e9f9ef; border: 1px solid #b7e7c9; color: #14713d; }
        .auth-flash--bad  { background: #fdecec; border: 1px solid #f5c2c2; color: #a4262c; }
        .auth-flash--warn { background: #fff8e1; border: 1px solid #f2dda4; color: #8a6100; }
        .auth-flash ul { margin: 0; }
        .social-login-buttons .social-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 42px;
            border-radius: 7px;
            font-weight: 500;
            letter-spacing: .01em;
            border: 0;
        }
        .social-login-btn .social-login-icon {
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 20px;
            font-size: 16px;
            line-height: 1;
        }
        .social-login-btn--google { color: #13233d; background: #e5e7eb; }
        .social-login-btn--google:hover { color: #13233d; background: #d9dde3; }
        .social-login-btn--google .social-login-icon svg { width: 18px; height: 18px; }
        .social-login-btn--facebook { color: #fff; background: #050505; }
        .social-login-btn--facebook:hover { color: #fff; background: #1b1b1b; }
        .social-login-btn--facebook .social-login-icon { color: #050505; background: #fff; border-radius: 50%; }
    </style>
</head>
<body dir="<?php echo e(strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl'); ?>">

<div class="auth-wrap">

    <aside class="auth-brand">
        <div class="inner">
            
            <?php
                $authLogoId = settings('dark_theme_logo') ?: settings('light_theme_logo');
                $authLogo   = $authLogoId ? \App\Models\Upload::find($authLogoId) : null;
                $authLogoOk = $authLogo && \Illuminate\Support\Facades\File::exists(public_path($authLogo->original));
            ?>
            <a href="<?php echo e(route('home')); ?>" class="wordmark">
                <?php if($authLogoOk): ?>
                    <img src="<?php echo e(asset($authLogo->original)); ?>" alt="<?php echo e($authBrandName); ?>" class="brand-logo-img">
                <?php else: ?>
                    <?php echo e($authBrandName); ?><span>.</span>
                <?php endif; ?>
            </a>
            <?php echo $__env->yieldContent('brand_heading'); ?>
            <?php if($authFeatures->isNotEmpty()): ?>
                <ul class="feat-list">
                    <?php $__currentLoopData = $authFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><span class="tick"><?php echo e(___('auth.brand_feature_icon')); ?></span> <?php echo e($feature); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="quote-box">
            <div class="stars mb-1"><?php echo e(___('auth.quote_stars')); ?></div>
            <p><?php echo e($authQuoteText); ?></p>
            <div class="who"><?php echo e($authQuoteName); ?></div>
            <div class="role"><?php echo e($authQuoteRole); ?></div>
        </div>
    </aside>

    <main class="auth-form-side">
        <div class="auth-card <?php echo $__env->yieldContent('card_class'); ?>">
            
            <?php $__currentLoopData = ['success' => 'ok', 'danger' => 'bad', 'error' => 'bad', 'warning' => 'warn']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(session()->has($key)): ?>
                    <div class="auth-flash auth-flash--<?php echo e($tone); ?>" role="alert"><?php echo e(session($key)); ?></div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($errors->any() && $errors->count() > 1): ?>
                <div class="auth-flash auth-flash--bad" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($message); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('form'); ?>
        </div>
    </main>

</div>

<script src="<?php echo e(asset('backend/libs/jquery/jquery-3.7.1.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/libs/sweetalert2/js/sweetalert2.all.v11.min.js')); ?>"></script>
<?php echo $__env->make('backend.partials.alert-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script src="<?php echo e(asset('backend/js/custom/flow-inline.js')); ?>?v=<?php echo e(filemtime(public_path('backend/js/custom/flow-inline.js'))); ?>"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/auth/saas-layout.blade.php ENDPATH**/ ?>