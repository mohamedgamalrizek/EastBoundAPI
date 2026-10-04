<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['key' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['key' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<?php
    $hiwRoute = \Illuminate\Support\Facades\Route::currentRouteName() ?? '';
    $hiwCandidates = $key ? [$key] : [];
    $hiwParts = array_values(array_filter(explode('.', $hiwRoute)));
    for ($i = count($hiwParts); $i >= 1; $i--) {
        $hiwCandidates[] = implode('.', array_slice($hiwParts, 0, $i));
    }
    // Flat lookup: keys like "tour.category" contain dots, so config() dot
    // notation would mis-read them as nesting.
    $hiwAll = config('how-it-works', []);
    $hiw = null;
    foreach ($hiwCandidates as $hiwCandidate) {
        if ($hiwCandidate && isset($hiwAll[$hiwCandidate])) {
            $hiw = $hiwAll[$hiwCandidate];
            break;
        }
    }
    $hiwId = 'hiw-' . substr(md5(($key ?? '') . '|' . $hiwRoute), 0, 10);
?>

<?php if($hiw): ?>
<div class="hiw">
    <button type="button" class="hiw-toggle" data-hiw-target="#<?php echo e($hiwId); ?>" aria-expanded="false" aria-controls="<?php echo e($hiwId); ?>">
        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        <span>How it works</span>
        <i class="fa fa-angle-down hiw-chevron" aria-hidden="true"></i>
    </button>
    <div class="hiw-panel" id="<?php echo e($hiwId); ?>">
        <div class="hiw-panel-clip">
            <div class="hiw-panel-inner">
                <h6 class="hiw-title"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo e($hiw['title'] ?? 'How it works'); ?></h6>
                <?php if(!empty($hiw['intro'])): ?>
                <p class="hiw-intro"><?php echo e($hiw['intro']); ?></p>
                <?php endif; ?>
                <?php if(!empty($hiw['steps'])): ?>
                <ol class="hiw-steps">
                    <?php $__currentLoopData = $hiw['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hiwStep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($hiwStep); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
                <?php endif; ?>
                <?php if(!empty($hiw['tips'])): ?>
                <div class="hiw-tips">
                    <?php $__currentLoopData = $hiw['tips']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hiwTip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p><i class="fa fa-lightbulb-o" aria-hidden="true"></i> <?php echo e($hiwTip); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('9a0fcd3b-ce16-47e2-bd0d-b40126bd1c90')): $__env->markAsRenderedOnce('9a0fcd3b-ce16-47e2-bd0d-b40126bd1c90'); ?>
<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('frontend/js/pages/how-it-works.js')); ?>?v=<?php echo e(filemtime(public_path('frontend/js/pages/how-it-works.js'))); ?>"></script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/components/how-it-works.blade.php ENDPATH**/ ?>