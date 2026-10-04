<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="viewport" content="width=device-width, minimum-scale=0.8, maximum-scale = 0.8, user-scalable = no , shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />

    <title> <?php echo $__env->yieldContent('title'); ?> </title>

    <!-- Favicon icon -->
    <link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="<?php echo e(favicon(settings('favicon'))); ?>">

    
    <link rel="stylesheet" href="<?php echo e(asset('fonts/fonts.css')); ?>" />

    
    <link rel="stylesheet" href="<?php echo e(asset('backend/libs/fontawesome6/css/all.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('backend/libs/fontawesome6/css/v4-shims.min.css')); ?>" />

    <link rel="stylesheet" href="<?php echo e(asset('backend/css/bootstrap.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('backend/css/custom.css')); ?>" />
    


    
    <link rel="stylesheet" href="<?php echo e(asset('backend/libs/select2-4.1/css/select2.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('backend/libs/flatpickr/flatpickr.min.css')); ?>">

    
    <link rel="stylesheet" href="<?php echo e(asset('backend/libs/datatables/css/dataTables.bootstrap4.min.css')); ?>" />

    
    <link rel="stylesheet" href="<?php echo e(asset('backend/css/sass/main.css')); ?>?v=<?php echo e(filemtime(public_path('backend/css/sass/main.css'))); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('backend/css/inline-utilities.css')); ?>?v=<?php echo e(filemtime(public_path('backend/css/inline-utilities.css'))); ?>" />

    
    

    <?php echo $__env->yieldPushContent('styles'); ?>

</head>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/partials/header.blade.php ENDPATH**/ ?>