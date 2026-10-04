<aside class="tv-sidebar j-custom-style" id="backendSidebar" aria-label="{{ ___('menus.staff_portal') }}">
    <div class="tv-sidebar-scroll">
        <ul class="tv-sidebar-menu" id="backendSidebarMenu">

            <li class="nav-label first">{{ ___('menus.staff_portal') }}</li>

            <li> <a href="{{ route('staff.dashboard') }}" class="{{ request()->routeIs('staff.dashboard') ? 'mm-active' : '' }}"> <i class="icon-speedometer"></i> <span class="nav-text">{{ ___('menus.dashboard') }}</span> </a> </li>
            <li> <a href="{{ route('staff.tasks') }}" class="{{ request()->routeIs('staff.tasks') ? 'mm-active' : '' }}"> <i class="icon-list"></i> <span class="nav-text">{{ ___('menus.my_tasks') }}</span> </a> </li>

            <li class="nav-label">{{ ___('menus.hr') }}</li>

            <li> <a href="{{ route('staff.attendance') }}" class="{{ request()->routeIs('staff.attendance') ? 'mm-active' : '' }}"> <i class="icon-clock"></i> <span class="nav-text">{{ ___('menus.attendance') }}</span> </a> </li>
            <li> <a href="{{ route('staff.leave') }}" class="{{ request()->routeIs('staff.leave*') ? 'mm-active' : '' }}"> <i class="icon-calendar"></i> <span class="nav-text">{{ ___('menus.leave_requests') }}</span> </a> </li>
            <li> <a href="{{ route('staff.payslips') }}" class="{{ request()->routeIs('staff.payslips') ? 'mm-active' : '' }}"> <i class="icon-wallet"></i> <span class="nav-text">{{ ___('menus.payslips') }}</span> </a> </li>

            <li class="nav-label">{{ ___('menus.more') }}</li>

            <li> <a href="{{ route('staff.profile') }}" class="{{ request()->routeIs('staff.profile*') ? 'mm-active' : '' }}"> <i class="icon-user"></i> <span class="nav-text">{{ ___('menus.my_profile') }}</span> </a> </li>

        </ul>
    </div>
</aside>
