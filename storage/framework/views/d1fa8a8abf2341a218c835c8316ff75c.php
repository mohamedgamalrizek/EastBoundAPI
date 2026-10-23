<?php $__env->startSection('title'); ?> Convert Lead to Hotel Booking <?php $__env->stopSection(); ?>
<?php $__env->startSection('maincontent'); ?>
<?php if (isset($component)) { $__componentOriginal940eccdfb725c280df391d84064ec972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal940eccdfb725c280df391d84064ec972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page','data' => ['title' => 'Convert Lead to Hotel Booking','breadcrumb' => ['CRM','Leads','Convert to Booking']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Convert Lead to Hotel Booking','breadcrumb' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['CRM','Leads','Convert to Booking'])]); ?>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="tv-card"><div class="tv-card-body">
                <div class="d-flex align-items-center">
                    <span class="tv-avatar tv-avatar-48 mr-3"><?php echo e(strtoupper(mb_substr($lead->name, 0, 1))); ?></span>
                    <div>
                        <h5 class="mb-0"><?php echo e($lead->name); ?></h5>
                        <small class="text-muted">
                            <?php echo e($lead->phone ?: '—'); ?>

                            <?php if($lead->email): ?> &middot; <?php echo e($lead->email); ?> <?php endif; ?>
                            &middot; Interest: <?php echo e($lead->interest); ?>

                        </small>
                    </div>
                    <a href="<?php echo e(route('crm.leads.show', $lead->id)); ?>" class="btn btn-sm btn-outline-primary ml-auto"><i class="fa fa-arrow-left"></i> Back to lead</a>
                </div>
                <hr>
                <p class="text-muted mb-0">
                    Pre-filled from this lead's enquiry — guest name, check-in/out dates and nights.
                    Pick the hotel, room and booking number to turn the request into a real booking.
                    The lead will be marked <b>Won</b> once saved.
                </p>
            </div></div>
        </div>
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="<?php echo e(route('crm.leads.store.hotel', $lead->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="lead_id" value="<?php echo e($lead->id); ?>">
                        <?php echo $__env->make('backend.hotel.booking._form', ['item' => $item, 'cancelRoute' => route('crm.leads.show', $lead->id)], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

<?php echo $__env->make('backend.partials.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/crm/lead-convert-hotel.blade.php ENDPATH**/ ?>