<?php $__env->startSection('title'); ?> Lead Details <?php $__env->stopSection(); ?>
<?php $__env->startSection('maincontent'); ?>
<?php if (isset($component)) { $__componentOriginal940eccdfb725c280df391d84064ec972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal940eccdfb725c280df391d84064ec972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page','data' => ['title' => 'Lead Details','breadcrumb' => ['CRM','Leads','Details']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Lead Details','breadcrumb' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['CRM','Leads','Details'])]); ?>
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tv-card"><div class="tv-card-body text-center">
                <span class="tv-avatar tv-avatar-96 mx-auto mb-2"><?php echo e(strtoupper(mb_substr($lead->name, 0, 1))); ?></span>
                <h4 class="mb-0"><?php echo e($lead->name); ?></h4>
                <?php echo $lead->stageBadge(); ?>

                <hr>
                <ul class="list-unstyled text-left">
                    <li class="mb-2"><i class="fa fa-phone text-muted mr-2"></i> <?php echo e($lead->phone ?: '—'); ?></li>
                    <li class="mb-2"><i class="fa fa-envelope text-muted mr-2"></i> <?php echo e($lead->email ?: '—'); ?></li>
                    <li class="mb-2"><i class="fa fa-bullhorn text-muted mr-2"></i> Source: <?php echo e($lead->source ?: '—'); ?></li>
                    <li class="mb-2"><i class="fa fa-globe text-muted mr-2"></i> Website: <?php echo e($lead->website ?: '—'); ?></li>
                    <li class="mb-2">Language: <?php echo e($lead->language ?: '—'); ?></li>
                    <li class="mb-2">Country: <?php echo e($lead->country ?: '—'); ?></li>
                    <li class="mb-2">Campaign Source: <?php echo e($lead->campaign_source ?: '—'); ?></li>
                    <li class="mb-2"><i class="fa fa-suitcase text-muted mr-2"></i> Interest: <?php echo e($lead->interest ?: '—'); ?></li>
                    <li class="mb-2"><i class="fa fa-user-tie text-muted mr-2"></i> Owner: <?php echo e($lead->owner ?: ($lead->assignedTo->name ?? '—')); ?></li>
                    <li class="mb-2"><i class="fa fa-money-bill text-muted mr-2"></i> Value: <?php echo e(currency_symbol()); ?><?php echo e(number_format($lead->value, 2)); ?></li>
                    <li class="mb-0">Created At: <?php echo e($lead->created_at?->format('Y-m-d H:i:s') ?? '—'); ?></li>
                </ul>
                <?php if($lead->notes): ?>
                <hr>
                <h5 class="text-left">Notes / Original Submission</h5>
                <div class="text-muted text-left mb-0" dir="auto"><?php echo nl2br(e($lead->notes)); ?></div>
                <?php endif; ?>
                <div class="d-flex justify-content-center mt-3 tv-action-gap-md">
                    <?php if($lead->phone): ?>
                        <a href="tel:<?php echo e($lead->phone); ?>" class="btn btn-sm btn-primary"><i class="fa fa-phone"></i> Call</a>
                    <?php endif; ?>
                    <?php if($lead->email): ?>
                        <a href="mailto:<?php echo e($lead->email); ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-envelope"></i> Email</a>
                    <?php endif; ?>
                    <?php if(hasPermission('crm_update') && str_contains(strtolower($lead->interest ?? ''), 'flight')): ?>
                        <a href="<?php echo e(route('crm.leads.convert.flight', $lead->id)); ?>" class="btn btn-sm btn-success"><i class="fa fa-plane"></i> Convert to Booking</a>
                    <?php endif; ?>
                    <?php if(hasPermission('crm_update') && str_contains(strtolower($lead->interest ?? ''), 'hotel')): ?>
                        <a href="<?php echo e(route('crm.leads.convert.hotel', $lead->id)); ?>" class="btn btn-sm btn-success"><i class="fa fa-hotel"></i> Convert to Hotel Booking</a>
                    <?php endif; ?>
                    <?php if(hasPermission('crm_update')): ?>
                        <a href="<?php echo e(route('crm.leads.edit', $lead->id)); ?>" class="btn btn-sm btn-outline-success"><i class="fa fa-edit"></i> Edit</a>
                    <?php endif; ?>
                </div>
            </div></div>
        </div>
        <div class="col-lg-8 mb-4">
            <div class="tv-card mb-4">
                <div class="tv-card-head"><h4 class="title-site mb-0">Trip Details</h4></div>
                <div class="tv-card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Subject</dt><dd class="col-sm-8"><?php echo e($lead->subject ?: '—'); ?></dd>
                        <dt class="col-sm-4">Destinations</dt><dd class="col-sm-8"><?php echo e($lead->destinations ?: '—'); ?></dd>
                        <dt class="col-sm-4">From Date</dt><dd class="col-sm-8"><?php echo e($lead->from_date?->format('Y-m-d') ?? '—'); ?></dd>
                        <dt class="col-sm-4">To Date</dt><dd class="col-sm-8"><?php echo e($lead->to_date?->format('Y-m-d') ?? '—'); ?></dd>
                        <dt class="col-sm-4">Duration (Days)</dt><dd class="col-sm-8"><?php echo e($lead->duration ?? '—'); ?></dd>
                        <dt class="col-sm-4">Adults</dt><dd class="col-sm-8"><?php echo e($lead->adults ?? '—'); ?></dd>
                        <dt class="col-sm-4">Children</dt><dd class="col-sm-8"><?php echo e($lead->children ?? '—'); ?></dd>
                        <dt class="col-sm-4">Budget</dt><dd class="col-sm-8"><?php echo e($lead->budget ?: '—'); ?></dd>
                    </dl>
                    <hr>
                    <h5>Customer Comments</h5>
                    <div dir="auto"><?php echo nl2br(e($lead->comments ?: '—')); ?></div>
                </div>
            </div>
            <div class="tv-card"><div class="tv-card-head"><h4 class="title-site mb-0">Activity Timeline</h4></div>
                <div class="tv-card-body">
                    <ul class="list-unstyled">
                        <?php
                            $iconMap = [
                                'Call'      => ['fa-phone', 'primary'],
                                'Email'     => ['fa-envelope', 'info'],
                                'WhatsApp'  => ['fa-comment-dots', 'success'],
                                'Meeting'   => ['fa-user', 'warning'],
                                'SMS'       => ['fa-comment', 'secondary'],
                            ];
                        ?>
                        <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php [$icon, $tone] = $iconMap[$a->channel] ?? ['fa-flag', 'secondary']; ?>
                        <li class="d-flex mb-4">
                            <div class="mr-3"><span class="tv-circle-icon-38 bg-<?php echo e($tone); ?> text-white"><i class="fa-solid <?php echo e($icon); ?>"></i></span></div>
                            <div>
                                <div class="d-flex justify-content-between"><b><?php echo e($a->subject); ?></b></div>
                                <?php if($a->body): ?>
                                    <div class="text-muted tv-text-sm"><?php echo e($a->body); ?></div>
                                <?php endif; ?>
                                <small class="text-muted">
                                    <?php echo e($a->activity_date?->format('M j, Y')); ?>

                                    <?php if($a->user): ?> &middot; <?php echo e($a->user->name); ?> <?php endif; ?>
                                </small>
                            </div>
                        </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="text-muted mb-4">No activity logged yet.</li>
                        <?php endif; ?>
                        <li class="d-flex">
                            <div class="mr-3"><span class="tv-circle-icon-38 bg-secondary text-white"><i class="fa-solid fa-flag"></i></span></div>
                            <div>
                                <div class="d-flex justify-content-between"><b>Lead created</b></div>
                                <div class="text-muted tv-text-sm">Came in via <?php echo e($lead->source ?: 'an unknown source'); ?><?php echo e($lead->website ? ' / '.$lead->website : ''); ?>.</div>
                                <small class="text-muted"><?php echo e($lead->created_at->format('M j, Y')); ?></small>
                            </div>
                        </li>
                    </ul>
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

<?php echo $__env->make('backend.partials.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/crm/lead-details.blade.php ENDPATH**/ ?>