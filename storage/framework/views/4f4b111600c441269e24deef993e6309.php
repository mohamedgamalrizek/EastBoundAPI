<?php $__env->startSection('title'); ?> Dashboard <?php $__env->stopSection(); ?>

<?php $__env->startSection('maincontent'); ?>
<?php
    $statusBadge = fn($s) => in_array(strtolower($s), ['confirmed','paid','completed','active']) ? 'success' : (in_array(strtolower($s), ['pending','processing']) ? 'warning' : (strtolower($s) === 'cancelled' ? 'danger' : 'info'));
?>

<div class="container-fluid tv-dash">

    
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-black">Welcome back, <?php echo e(auth()->user()->name); ?> 👋</h4>
            <p class="text-muted mb-3 mb-md-0">Here's what's happening across your agency today.</p>
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
        <div class="tv-date-chip d-flex align-items-center"><i class="fa-solid fa-calendar-day mr-2"></i><?php echo e(now()->format('l, d M Y')); ?></div>
    </div>

    
    <?php
        $kpis = [
            ['label'=>'Total Bookings','value'=>number_format($totalBookings),'icon'=>'fa-suitcase-rolling','grad'=>'linear-gradient(135deg,#6366f1,#8b5cf6)','delta'=>$bookingsDelta,'sub'=>'vs last month'],
            ['label'=>'Revenue','value'=>currency_symbol().number_format($revenue),'icon'=>'fa-sack-dollar','grad'=>'linear-gradient(135deg,#059669,#10b981)','delta'=>$revenueDelta,'sub'=>'vs last month'],
            ['label'=>'Visa Approvals','value'=>number_format($visaApproved),'icon'=>'fa-passport','grad'=>'linear-gradient(135deg,#0ea5e9,#06b6d4)','delta'=>null,'sub'=>'approved total'],
            ['label'=>'Open Leads','value'=>number_format($openLeads),'icon'=>'fa-user-plus','grad'=>'linear-gradient(135deg,#f59e0b,#f97316)','delta'=>null,'sub'=>'in pipeline'],
        ];
    ?>
    <div class="row g-3 mb-4">
        <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
            <div class="tv-kpi" style="--grad:linear-gradient(135deg,#A81A20,#D45B60)">
                <div class="tv-kpi-glow"></div>
                <div class="d-flex align-items-start justify-content-between">
                    <div class="tv-kpi-ico"><i class="fa-solid <?php echo e($k['icon']); ?>"></i></div>
                    <?php if(!is_null($k['delta'])): ?>
                        <span class="tv-trend <?php echo e($k['delta'] >= 0 ? 'up' : 'down'); ?>">
                            <i class="fa-solid <?php echo e($k['delta'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'); ?>"></i>
                            <?php echo e(abs($k['delta'])); ?>%
                        </span>
                    <?php endif; ?>
                </div>
                <div class="tv-kpi-val"><?php echo e($k['value']); ?></div>
                <div class="tv-kpi-label"><?php echo e($k['label']); ?></div>
                <div class="tv-kpi-sub"><?php echo e($k['sub']); ?></div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="tv-card h-100">
                <div class="tv-card-head">
                    <div>
                        <h5 class="mb-0">Revenue Overview</h5>
                        <span class="text-muted fs-13">Monthly performance · <?php echo e(now()->year); ?></span>
                    </div>
                    <div class="tv-legend">
                        <span><i class="dot tv-dot-revenue"></i> Revenue</span>
                        <span><i class="dot tv-dot-bookings"></i> Bookings</span>
                    </div>
                </div>
                <div class="tv-card-body"><div id="revenueChart"></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h5 class="mb-0">Bookings by Status</h5></div>
                <div class="tv-card-body d-flex align-items-center"><div id="typeChart" class="w-100"></div></div>
            </div>
        </div>
    </div>

    
    <?php
        $mini = [
            ['label'=>'Customers','value'=>number_format($customers),'icon'=>'fa-users','c'=>'#6366f1'],
            ['label'=>'Paid Revenue','value'=>currency_symbol().number_format($paidRevenue),'icon'=>'fa-circle-check','c'=>'#10b981'],
            ['label'=>'Avg. Booking','value'=>currency_symbol().number_format($avgBooking),'icon'=>'fa-receipt','c'=>'#0ea5e9'],
            ['label'=>'Conversion','value'=>$conversion.'%','icon'=>'fa-bullseye','c'=>'#f59e0b'],
        ];
    ?>
    <div class="row g-3 mb-4">
        <?php $__currentLoopData = $mini; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-xl-3 col-md-6">
            <div class="tv-mini">
                <div class="tv-mini-ico" style="background:<?php echo e($m['c']); ?>1a;color:<?php echo e($m['c']); ?>"><i class="fa-solid <?php echo e($m['icon']); ?>"></i></div>
                <div>
                    <div class="tv-mini-val"><?php echo e($m['value']); ?></div>
                    <div class="tv-mini-label"><?php echo e($m['label']); ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="tv-card h-100">
                <div class="tv-card-head">
                    <h5 class="mb-0">Recent Bookings</h5>
                    <a href="<?php echo e(route('booking.index')); ?>" class="tv-link">View all <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="tv-card-body">
                    <div class="table-responsive">
                        <table class="table tv-table mb-0">
                            <thead><tr><th>Booking</th><th>Customer</th><th>Package</th><th>Amount</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $recentBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><span class="fw-semibold">BKG-<?php echo e(str_pad($b->id, 5, '0', STR_PAD_LEFT)); ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="tv-avatar flex-shrink-0 mr-2"><?php echo e(strtoupper(mb_substr($b->customer_name ?? 'G', 0, 1))); ?></span>
                                            <?php echo e($b->customer_name); ?>

                                        </div>
                                    </td>
                                    <td class="text-muted"><?php echo e(optional($b->package)->title ?? '—'); ?></td>
                                    <td class="fw-semibold"><?php echo e(currency_symbol()); ?><?php echo e(number_format($b->amount)); ?></td>
                                    <td><span class="bullet-badge bullet-badge-<?php echo e($statusBadge($b->status)); ?>"><?php echo e(ucfirst($b->status)); ?></span></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No bookings yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h5 class="mb-0">Top Destinations</h5></div>
                <div class="tv-card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $topDestinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $pct = $totalBookings ? round($d->tours / $totalBookings * 100) : 0; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold"><i class="fa-solid fa-location-dot text-primary me-1"></i><?php echo e($d->destination ?? 'Unknown'); ?></span>
                                <span class="text-muted fs-13"><?php echo e($d->tours); ?> · <?php echo e(currency_symbol()); ?><?php echo e(number_format($d->revenue)); ?></span>
                            </div>
                            <div class="tv-progress"><div class="tv-progress-bar" style="width:<?php echo e($pct); ?>%"></div></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted mb-0">No booking data yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script type="application/json" id="flow-data">
{
  "charts": [
    {
      "target": "#revenueChart",
      "series": [
        { "name": "Revenue", "type": "area", "data": <?php echo json_encode($monthlyRevenue); ?> },
        { "name": "Bookings", "type": "line", "data": <?php echo json_encode($monthlyBookings); ?> }
      ],
      "categories": <?php echo json_encode($monthLabels); ?>

    },
    {
      "target": "#typeChart",
      "labels": <?php echo json_encode($bookingsByStatus->keys()); ?>,
      "series": <?php echo json_encode($bookingsByStatus->values()); ?>

    }
  ]
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('backend.partials.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/dashboard.blade.php ENDPATH**/ ?>