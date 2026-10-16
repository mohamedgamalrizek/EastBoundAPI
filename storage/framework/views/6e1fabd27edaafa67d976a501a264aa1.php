<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => '', 'breadcrumb' => [], 'action' => null]));

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

foreach (array_filter((['title' => '', 'breadcrumb' => [], 'action' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>" class="breadcrumb-link">Dashboard</a></li>
                            <?php $__currentLoopData = $breadcrumb; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $crumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active"><?php echo e($crumb); ?></a></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ol>
                    </nav>
                </div>
                <?php if (isset($component)) { $__componentOriginal94c28860a1a2364f6fd1663082f160f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal94c28860a1a2364f6fd1663082f160f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.how-it-works','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('how-it-works'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal94c28860a1a2364f6fd1663082f160f8)): ?>
<?php $attributes = $__attributesOriginal94c28860a1a2364f6fd1663082f160f8; ?>
<?php unset($__attributesOriginal94c28860a1a2364f6fd1663082f160f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal94c28860a1a2364f6fd1663082f160f8)): ?>
<?php $component = $__componentOriginal94c28860a1a2364f6fd1663082f160f8; ?>
<?php unset($__componentOriginal94c28860a1a2364f6fd1663082f160f8); ?>
<?php endif; ?>
                <?php if(isset($action)): ?>
                <div><?php echo e($action); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php echo e($slot); ?>

</div>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/components/page.blade.php ENDPATH**/ ?>