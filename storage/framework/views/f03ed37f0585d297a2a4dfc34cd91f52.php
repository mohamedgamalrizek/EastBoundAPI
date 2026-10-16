<?php $__env->startSection('title'); ?> Contact Messages <?php $__env->stopSection(); ?>
<?php $__env->startSection('maincontent'); ?>
<?php if (isset($component)) { $__componentOriginal940eccdfb725c280df391d84064ec972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal940eccdfb725c280df391d84064ec972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page','data' => ['title' => 'Contact Messages','breadcrumb' => ['CRM','Contact Messages']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Contact Messages','breadcrumb' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['CRM','Contact Messages'])]); ?>

    <?php if (isset($component)) { $__componentOriginalc8463834ba515134d5c98b88e1a9dc03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8463834ba515134d5c98b88e1a9dc03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-table','data' => ['headers' => ['Name','Contact','Subject','Website','Message','Status','Submitted','Action']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Name','Contact','Subject','Website','Message','Status','Submitted','Action'])]); ?>
        <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr id="row_<?php echo e($message->id); ?>">
                <td><b><?php echo e($message->name); ?></b></td>
                <td>
                    <?php if($message->email): ?>
                        <div><a href="mailto:<?php echo e($message->email); ?>"><?php echo e($message->email); ?></a></div>
                    <?php endif; ?>
                    <?php if($message->phone): ?>
                        <div><a href="tel:<?php echo e(preg_replace('/[^\d+]/', '', $message->phone)); ?>"><?php echo e($message->phone); ?></a></div>
                    <?php endif; ?>
                    <?php if(!$message->email && !$message->phone): ?>
                        <span class="text-muted">No contact</span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($message->subject ?: 'General enquiry'); ?></td>
                <td><?php echo e($message->website ?: '—'); ?></td>
                <td><?php echo e(\Illuminate\Support\Str::limit($message->message, 80)); ?></td>
                <td><?php echo $message->statusBadge(); ?></td>
                <td><?php echo e($message->created_at?->format('d M Y, h:i A')); ?></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="<?php echo e(route('crm.contact-messages.show', $message->id)); ?>" class="btn btn-sm btn-outline-primary" title="<?php echo e(___('label.view')); ?>"><i class="fa fa-eye"></i></a>
                        <?php if(hasPermission('crm_delete')): ?>
                        <a class="btn btn-sm btn-outline-danger" href="<?php echo e(route('crm.contact-messages.delete', $message->id)); ?>" onclick="tryDelete(event)" data-remove-id="row_<?php echo e($message->id); ?>" data-title="<?php echo e(___('label.delete')); ?>" data-text="<?php echo e(___('alert.this_action_cannot_be_reversed')); ?>" data-confirm-button-text="<?php echo e(___('label.delete')); ?>" data-cancel-button-text="<?php echo e(___('label.cancel')); ?>"><i class="fa fa-trash"></i></a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc8463834ba515134d5c98b88e1a9dc03)): ?>
<?php $attributes = $__attributesOriginalc8463834ba515134d5c98b88e1a9dc03; ?>
<?php unset($__attributesOriginalc8463834ba515134d5c98b88e1a9dc03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc8463834ba515134d5c98b88e1a9dc03)): ?>
<?php $component = $__componentOriginalc8463834ba515134d5c98b88e1a9dc03; ?>
<?php unset($__componentOriginalc8463834ba515134d5c98b88e1a9dc03); ?>
<?php endif; ?>

    <?php if($messages->count()): ?>
        <?php if (isset($component)) { $__componentOriginal786c1b77feb5ee42ab9f9bf75faacbf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal786c1b77feb5ee42ab9f9bf75faacbf1 = $attributes; } ?>
<?php $component = App\View\Components\PaginateShow::resolve(['items' => $messages] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

<?php echo $__env->make('backend.partials.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/contact_message/index.blade.php ENDPATH**/ ?>