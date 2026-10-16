
<?php
    $tone = trim($__env->yieldContent('tone')) ?: 'danger';
    $tone = in_array($tone, ['danger', 'warning', 'info'], true) ? $tone : 'danger';

    // Send signed-in users back to the panel their role can actually open,
    // otherwise a 403 "Back to Home" just bounces them into another 403.
    try {
        $homeUrl = auth()->check() && method_exists(auth()->user(), 'home')
            ? auth()->user()->home()
            : url('/');
    } catch (\Throwable $e) {
        $homeUrl = url('/');
    }

    try { $iconHref = favicon(); } catch (\Throwable $e) { $iconHref = null; }
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="tone-<?php echo e($tone); ?>">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title'); ?> — <?php echo e(config('app.name', 'FLOW')); ?></title>
    <?php if($iconHref): ?>
        <link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="<?php echo e($iconHref); ?>">
    <?php endif; ?>

    
    <link rel="stylesheet" href="<?php echo e(asset('errors/css/layout.css')); ?>">
</head>

<body>
    <main class="err-card" role="alert">
        <div class="err-icon" aria-hidden="true"><?php echo $__env->yieldContent('glyph', '!'); ?></div>

        <h1 class="err-code"><?php echo $__env->yieldContent('code'); ?></h1>
        <h2 class="err-heading"><?php echo $__env->yieldContent('heading'); ?></h2>
        <p class="err-message"><?php echo $__env->yieldContent('message'); ?></p>

        <div class="err-actions">
            <a class="err-btn err-btn-primary" href="<?php echo e($homeUrl); ?>">Back to Home</a>
            <button type="button" class="err-btn err-btn-ghost" onclick="history.back()">Go Back</button>
        </div>

        <?php if (! empty(trim($__env->yieldContent('extra')))): ?>
            <div class="err-foot"><?php echo $__env->yieldContent('extra'); ?></div>
        <?php endif; ?>
    </main>
</body>

</html>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/errors/layout.blade.php ENDPATH**/ ?>