<!--**********************************            Nav header start        ***********************************-->
<div class="nav-header">

    <a href="<?php echo e(url('/')); ?>" class="brand-logo"> <img src="<?php echo e(logo(settings('light_theme_logo'),'original')); ?>" alt="Logo" class="img-fluid" /> </a>
    <a href="<?php echo e(url('/')); ?>" class="logo-icon"> <img src="<?php echo e(favicon(settings('favicon'))); ?>" alt="Logo" class="w-100" /> </a>

    <button class="tv-sidebar-toggle" id="backendSidebarToggle" type="button" aria-label="Toggle sidebar" aria-controls="backendSidebar" aria-expanded="true">
        <span class="hamburger ham-nav" aria-hidden="true">
            <i class="jjj-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5v14m18-7H7m8 6l6-6l-6-6" />
                </svg>
            </i>
            <i class="jjj-right">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 6l-6 6l6 6m-6-6h14m4 7V5" />
                </svg>
            </i>

        </span>
    </button>
</div>



<!--**********************************            Nav header end        ***********************************-->

<!--**********************************            Header start        ***********************************-->

<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="j-header-container">
                    <div class="j-header-content">
                        <?php if(auth()->check() && auth()->user()->isAdminUser()): ?>
                        <div class="j-search">
                            <form class="j-search-form" action="<?php echo e(route('search')); ?>">
                                <input class="j-form-control" type="text" placeholder="<?php echo e(___('label.search')); ?>" name="q" id="search" list="route_list" data-url="<?php echo e(route('search.route')); ?>">
                                <datalist id="route_list"> </datalist>
                                <button type="submit" class="j-form-btn"> <i class="icon-magnifier"></i> </button>
                            </form>
                        </div>
                        <?php endif; ?>

                        <div class="nav-lang">
                            <div class="dropdown custom-dropdown">
                                <button type="button" class="btn-ami text-black" data-toggle="dropdown">
                                    <span> <i class="<?php echo e(defaultLanguage()->icon_class); ?>"></i> <?php echo e(Str::upper(defaultLanguage()->code)); ?> <i class="fa fa-angle-down"></i> </span>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right">
                                    <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a class="dropdown-item" href="<?php echo e(route('setLocalization',$lang->code)); ?>"> <span class="flg-lfex"> <i class="<?php echo e(@$lang->icon_class); ?>"></i> <?php echo e(@$lang->name); ?> </span> </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                            </div>
                        </div>

                        
                        <div class="nav-bell">
                            <a class="j-nav-lk text-black tv-icon-btn-36" href="<?php echo e(route('home')); ?>" target="_blank" rel="noopener"
                               title="<?php echo e(___('label.visit_website')); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3.6 9h16.8M3.6 15h16.8"/><path d="M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18"/></svg>
                            </a>
                        </div>

                        <?php if(hasPermission('dashboard_read')): ?>
                        
                        <div class="nav-bell">
                            <a class="j-nav-lk text-black tv-icon-btn-36" href="<?php echo e(route('cache.clear')); ?>"
                               title="<?php echo e(___('label.clear_cache')); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                            </a>
                        </div>
                        <?php endif; ?>

                        <div class="day-night nav-bell">
                            <a class="j-nav-lk text-black tv-icon-btn-36" href="#" id="darkModeToggle" title="<?php echo e(___('label.toggle_dark_mode')); ?>">
                                
                                <svg id="darkModeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 5h4m-2-2v4m.985 5.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/></svg>
                            </a>
                        </div>

                        <div class="dropdown notification_dropdown">
                            <a class="j-nav-lk nav-bell text-black position-relative" href="#" role="button" data-toggle="dropdown">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="M10.268 21a2 2 0 0 0 3.464 0M11.68 2.009A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673c-.824-.85-1.678-1.731-2.21-3.348"/><circle cx="18" cy="5" r="3"/></g></svg>
                                <span id="notificationUnreadBadge" class="badge badge-danger tv-badge-corner d-none"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right">
                                <div class="d-flex justify-content-end px-3 pt-2">
                                    <a href="#" id="markAllReadLink" class="small">Mark all as read</a>
                                </div>
                                <ul class="list-unstyled" id="notificationList">
                                    <li class="media dropdown-item text-center text-muted">Loading…</li>
                                </ul>
                                <a class="all-notification" href="<?php echo e(route('notifications.index')); ?>"> <?php echo e(___('label.see_all_notifications')); ?> <i class="ti-arrow-right"></i> </a>
                            </div>
                        </div>


                        <div class="dropdown header-profile">
                            <a class="nav-np" href="#" role="button" data-toggle="dropdown"> <img src="<?php echo e(getImage(auth()->user()->image,'original')); ?>" class="np" alt="" />
                                <h6 class="heading-6 mb-0 text-black"> <?php echo e(Auth::user()->name); ?> </h6>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="<?php echo e(route('profile')); ?>" class="dropdown-item"> <i class="icon-user"></i> <span class="ml-2"><?php echo e(___('menus.profile')); ?> </span> </a>

                                <a href="<?php echo e(route('password.edit')); ?>" class="dropdown-item"> <i class=" icon-key"></i> <span class="ml-2"><?php echo e(___('menus.change_password')); ?> </span> </a>

                                <a href="<?php echo e(route('logout')); ?>" class="dropdown-item" onclick="event.preventDefault();document.getElementById('logout-form').submit();"> <i class="icon-logout"></i> <span class="ml-2"><?php echo e(___('label.Logout')); ?> </span> </a>

                                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                                    <?php echo csrf_field(); ?>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</div>




<?php $__env->startPush('scripts'); ?>

<script src="<?php echo e(asset('backend/js/navber.js')); ?>"></script>
<script>
    window.notificationRoutes = {
        dropdown:   <?php echo json_encode(route('notifications.dropdown'), 15, 512) ?>,
        unreadCount: <?php echo json_encode(route('notifications.unread-count'), 15, 512) ?>,
        markRead:   <?php echo json_encode(url('notifications'), 15, 512) ?> + '/',
        markAllRead: <?php echo json_encode(route('notifications.read-all'), 15, 512) ?>,
    };
</script>
<script src="<?php echo e(asset('backend/js/custom/notifications.js')); ?>?v=<?php echo e(filemtime(public_path('backend/js/custom/notifications.js'))); ?>"></script>

<?php $__env->stopPush(); ?>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/partials/navbar.blade.php ENDPATH**/ ?>