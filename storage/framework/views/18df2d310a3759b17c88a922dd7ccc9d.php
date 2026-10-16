<?php $__env->startSection('title'); ?> Create Lead <?php $__env->stopSection(); ?>
<?php $__env->startSection('maincontent'); ?>
<?php if (isset($component)) { $__componentOriginal940eccdfb725c280df391d84064ec972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal940eccdfb725c280df391d84064ec972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page','data' => ['title' => 'Create Lead','breadcrumb' => ['CRM','Leads','Create']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Create Lead','breadcrumb' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['CRM','Leads','Create'])]); ?>
    <div class="row"><div class="col-lg-12">
        <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site">New Lead</h4>
                </div>
                <div class="tv-card-body">
            <form action="<?php echo e(route('crm.leads.store')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <?php echo $__env->make('backend.crm._lead-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="mt-3">
                    <button type="submit" class="j-td-btn">Save Lead</button>
                    <a href="<?php echo e(route('crm.leads')); ?>" class="j-td-btn btn-red">Cancel</a>
                </div>
            </form>
        </div></div>
    </div></div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal940eccdfb725c280df391d84064ec972)): ?>
<?php $attributes = $__attributesOriginal940eccdfb725c280df391d84064ec972; ?>
<?php unset($__attributesOriginal940eccdfb725c280df391d84064ec972); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal940eccdfb725c280df391d84064ec972)): ?>
<?php $component = $__componentOriginal940eccdfb725c280df391d84064ec972; ?>
<?php unset($__componentOriginal940eccdfb725c280df391d84064ec972); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.partials.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/crm/lead-create.blade.php ENDPATH**/ ?>