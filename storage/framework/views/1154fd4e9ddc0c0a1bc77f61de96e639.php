<?php $__env->startSection('title'); ?> Leads <?php $__env->stopSection(); ?>
<?php $__env->startSection('maincontent'); ?>
<?php if (isset($component)) { $__componentOriginal940eccdfb725c280df391d84064ec972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal940eccdfb725c280df391d84064ec972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page','data' => ['title' => 'Leads','breadcrumb' => ['CRM','Leads']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Leads','breadcrumb' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['CRM','Leads'])]); ?>
     <?php $__env->slot('action', null, []); ?> 
        <a href="<?php echo e(route('crm.leads.create')); ?>" class="btn btn-primary"><i class="fa fa-plus mr-1"></i> Add Lead</a>
     <?php $__env->endSlot(); ?>

    <div class="row"><div class="col-12">
        <div class="tv-card">
            <div class="tv-card-head"><h4 class="title-site mb-0">All Leads</h4></div>
            <div class="tv-card-body">
                <div class="table-responsive">
                    <table class="table table-responsive-sm">
                        <thead class="bg">
                            <tr><th>Contact</th><th>Interest</th><th>Source</th><th>Language / Country</th><th>CSource</th><th>Travel Dates</th><th>Travelers</th><th>Budget</th><th>Stage</th><th>Owner</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $leads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr id="row_<?php echo e($lead->id); ?>">
                                <td>
                                    <a href="<?php echo e(route('crm.leads.show', $lead->id)); ?>"><?php echo e($lead->name); ?></a>
                                    <div class="text-muted"><small><?php echo e($lead->phone ?: '—'); ?></small></div>
                                    <div class="text-muted"><small><?php echo e($lead->email ?: '—'); ?></small></div>
                                </td>
                                <td>
                                    <div><?php echo e($lead->interest ?: '—'); ?></div>
                                    <small class="d-block text-muted">Subject: <?php echo e($lead->subject ?: '—'); ?></small>
                                    <small class="d-block text-muted">Destinations: <?php echo e($lead->destinations ?: '—'); ?></small>
                                </td>
                                <td><div><?php echo e($lead->source); ?></div><small class="text-muted"><?php echo e($lead->website ?: '—'); ?></small></td>
                                <td><div><?php echo e($lead->language ?: '—'); ?></div><small class="text-muted"><?php echo e($lead->country ?: '—'); ?></small></td>
                                <td><?php echo e($lead->campaign_source ?: '—'); ?></td>
                                <td>
                                    <div><?php echo e($lead->from_date?->format('Y-m-d') ?? '—'); ?> → <?php echo e($lead->to_date?->format('Y-m-d') ?? '—'); ?></div>
                                    <small class="text-muted">Days: <?php echo e($lead->duration ?? '—'); ?></small>
                                </td>
                                <td><div>Adults: <?php echo e($lead->adults ?? '—'); ?></div><small class="text-muted">Children: <?php echo e($lead->children ?? '—'); ?></small></td>
                                <td><div><?php echo e(currency_symbol()); ?><?php echo e(number_format($lead->value, 2)); ?></div><small class="text-muted">Budget: <?php echo e($lead->budget ?: '—'); ?></small></td>
                                <td><?php echo $lead->stageBadge(); ?></td>
                                <td><?php echo e($lead->owner ?? '—'); ?></td>
                                <td>
                                    <div class="input-group">
                                        <div class="input-group-prepend be-addon">
                                            <div class="d-flex align-items-center flex-wrap tv-action-gap">
                                                <a href="<?php echo e(route('crm.leads.show', $lead->id)); ?>" class="btn btn-sm btn-outline-secondary" title="View"><i class="fa fa-eye"></i></a>
                                                <a href="<?php echo e(route('crm.leads.edit', $lead->id)); ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                <a class="btn btn-sm btn-outline-danger" href="<?php echo e(route('crm.leads.delete', $lead->id)); ?>"
                                                    onclick="tryDelete(event)"
                                                    data-remove-id="row_<?php echo e($lead->id); ?>"
                                                    data-title="Delete" data-text="This action cannot be reversed."
                                                    data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <?php if (isset($component)) { $__componentOriginal93638a3e61f905a02a8f313b459b785c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93638a3e61f905a02a8f313b459b785c = $attributes; } ?>
<?php $component = App\View\Components\NodataFound::resolve(['colspan' => 13] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('nodata-found'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\NodataFound::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal93638a3e61f905a02a8f313b459b785c)): ?>
<?php $attributes = $__attributesOriginal93638a3e61f905a02a8f313b459b785c; ?>
<?php unset($__attributesOriginal93638a3e61f905a02a8f313b459b785c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal93638a3e61f905a02a8f313b459b785c)): ?>
<?php $component = $__componentOriginal93638a3e61f905a02a8f313b459b785c; ?>
<?php unset($__componentOriginal93638a3e61f905a02a8f313b459b785c); ?>
<?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(count($leads)): ?>
                <?php if (isset($component)) { $__componentOriginal786c1b77feb5ee42ab9f9bf75faacbf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal786c1b77feb5ee42ab9f9bf75faacbf1 = $attributes; } ?>
<?php $component = App\View\Components\PaginateShow::resolve(['items' => $leads] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('paginate-show'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PaginateShow::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal786c1b77feb5ee42ab9f9bf75faacbf1)): ?>
<?php $attributes = $__attributesOriginal786c1b77feb5ee42ab9f9bf75faacbf1; ?>
<?php unset($__attributesOriginal786c1b77feb5ee42ab9f9bf75faacbf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal786c1b77feb5ee42ab9f9bf75faacbf1)): ?>
<?php $component = $__componentOriginal786c1b77feb5ee42ab9f9bf75faacbf1; ?>
<?php unset($__componentOriginal786c1b77feb5ee42ab9f9bf75faacbf1); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
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

<?php echo $__env->make('backend.partials.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/crm/leads.blade.php ENDPATH**/ ?>