<!--**********************************            Nav header start        ***********************************-->
<div class="nav-header">

    <a href="{{ url('/')}}" class="brand-logo"> <img src="{{ logo(settings('light_theme_logo'),'original') }}" alt="Logo" class="img-fluid" /> </a>
    <a href="{{ url('/')}}" class="logo-icon"> <img src="{{ favicon(settings('favicon')) }}" alt="Logo" class="w-100" /> </a>

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
                        @if(auth()->check() && auth()->user()->isAdminUser())
                        <div class="j-search">
                            <form class="j-search-form" action="{{ route('search') }}">
                                <input class="j-form-control" type="text" placeholder="{{ ___('label.search') }}" name="q" id="search" list="route_list" data-url="{{ route('search.route') }}">
                                <datalist id="route_list"> </datalist>
                                <button type="submit" class="j-form-btn"> <i class="icon-magnifier"></i> </button>
                            </form>
                        </div>
                        @endif

                        <div class="nav-lang">
                            <div class="dropdown custom-dropdown">
                                <button type="button" class="btn-ami text-black" data-toggle="dropdown">
                                    <span> <i class="{{ defaultLanguage()->icon_class }}"></i> {{ Str::upper(defaultLanguage()->code) }} <i class="fa fa-angle-down"></i> </span>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right">
                                    @foreach ($languages as $lang)
                                    <a class="dropdown-item" href="{{ route('setLocalization',$lang->code) }}"> <span class="flg-lfex"> <i class="{{ @$lang->icon_class }}"></i> {{ @$lang->name }} </span> </a>
                                    @endforeach
                                </div>

                            </div>
                        </div>

                        {{-- Open the public site in a new tab — admins were
                             having to retype the URL to check their changes. --}}
                        <div class="nav-bell">
                            <a class="j-nav-lk text-black tv-icon-btn-36" href="{{ route('home') }}" target="_blank" rel="noopener"
                               title="{{ ___('label.visit_website') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3.6 9h16.8M3.6 15h16.8"/><path d="M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18"/></svg>
                            </a>
                        </div>

                        @if(hasPermission('dashboard_read'))
                        {{-- Clear caches without dropping to a terminal. --}}
                        <div class="nav-bell">
                            <a class="j-nav-lk text-black tv-icon-btn-36" href="{{ route('cache.clear') }}"
                               title="{{ ___('label.clear_cache') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                            </a>
                        </div>
                        @endif

                        <div class="day-night nav-bell">
                            <a class="j-nav-lk text-black tv-icon-btn-36" href="#" id="darkModeToggle" title="{{ ___('label.toggle_dark_mode') }}">
                                {{-- <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg> --}}
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
                                <a class="all-notification" href="{{ route('notifications.index') }}"> {{ ___('label.see_all_notifications') }} <i class="ti-arrow-right"></i> </a>
                            </div>
                        </div>


                        <div class="dropdown header-profile">
                            <a class="nav-np" href="#" role="button" data-toggle="dropdown"> <img src="{{ getImage(auth()->user()->image,'original') }}" class="np" alt="" />
                                <h6 class="heading-6 mb-0 text-black"> {{Auth::user()->name}} </h6>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="{{route('profile')}}" class="dropdown-item"> <i class="icon-user"></i> <span class="ml-2">{{ ___('menus.profile') }} </span> </a>

                                <a href="{{route('password.edit')}}" class="dropdown-item"> <i class=" icon-key"></i> <span class="ml-2">{{ ___('menus.change_password') }} </span> </a>

                                <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault();document.getElementById('logout-form').submit();"> <i class="icon-logout"></i> <span class="ml-2">{{ ___('label.Logout') }} </span> </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</div>


{{-- @include('backend.todo.to_do_list') --}}

@push('scripts')

<script src="{{ asset('backend/js/navber.js') }}"></script>
<script>
    window.notificationRoutes = {
        dropdown:   @json(route('notifications.dropdown')),
        unreadCount: @json(route('notifications.unread-count')),
        markRead:   @json(url('notifications')) + '/',
        markAllRead: @json(route('notifications.read-all')),
    };
</script>
<script src="{{ asset('backend/js/custom/notifications.js') }}?v={{ filemtime(public_path('backend/js/custom/notifications.js')) }}"></script>

@endpush
