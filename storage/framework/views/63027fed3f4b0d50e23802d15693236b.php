<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id'      => 'dt_' . uniqid(),
    'headers' => [],
    'pageLength' => 10,
    'order'   => '[]',
    'card'    => true,
]));

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

foreach (array_filter(([
    'id'      => 'dt_' . uniqid(),
    'headers' => [],
    'pageLength' => 10,
    'order'   => '[]',
    'card'    => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<?php if($card): ?>
<div class="tv-card tv-list-card">
    <div class="tv-card-body">
<?php endif; ?>

<table id="<?php echo e($id); ?>" class="table table-hover table-striped dt-table tv-table w-100"
        data-dt-page-length="<?php echo e((int) $pageLength); ?>"
        data-dt-order='<?php echo str_replace(["'", '"'], '&quot;', $order); ?>'>
    <thead class="bg">
        <tr>
            <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th><?php echo e($h); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
    </thead>
    <tbody>
        <?php echo e($slot); ?>

    </tbody>
</table>

<?php if($card): ?>
    </div>
</div>
<?php endif; ?>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/components/data-table.blade.php ENDPATH**/ ?>