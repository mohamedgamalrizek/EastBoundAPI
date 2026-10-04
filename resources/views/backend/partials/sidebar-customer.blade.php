<aside class="tv-sidebar j-custom-style" id="backendSidebar" aria-label="{{ ___('menus.my_account') }}">
    <div class="tv-sidebar-scroll">
        <ul class="tv-sidebar-menu" id="backendSidebarMenu">

            <li class="nav-label first">{{ ___('menus.my_account') }}</li>

            <li> <a href="{{ route('cust.dashboard') }}" class="{{ request()->routeIs('cust.dashboard') ? 'mm-active' : '' }}"> <i class="icon-speedometer"></i> <span class="nav-text">{{ ___('menus.dashboard') }}</span> </a> </li>
            <li> <a href="{{ route('cust.passport') }}" class="{{ request()->routeIs('cust.passport') ? 'mm-active' : '' }}"> <i class="icon-credit-card"></i> <span class="nav-text">{{ ___('menus.passport_info') }}</span> </a> </li>
            <li> <a href="{{ route('cust.travelers') }}" class="{{ request()->routeIs('cust.travelers') ? 'mm-active' : '' }}"> <i class="icon-people"></i> <span class="nav-text">{{ ___('menus.travelers') }}</span> </a> </li>
            <li> <a href="{{ route('cust.trip-planner') }}" class="{{ request()->routeIs('cust.trip-planner*') ? 'mm-active' : '' }}"> <i class="icon-map"></i> <span class="nav-text">AI Trip Planner</span> </a> </li>
            <li> <a href="{{ route('cust.loyalty') }}" class="{{ request()->routeIs('cust.loyalty') ? 'mm-active' : '' }}"> <i class="icon-star"></i> <span class="nav-text">Points & Referrals</span> </a> </li>

            <li class="nav-label">{{ ___('menus.my_travel') }}</li>

            <li> <a href="{{ route('cust.bookings') }}" class="{{ request()->routeIs('cust.bookings') ? 'mm-active' : '' }}"> <i class="icon-handbag"></i> <span class="nav-text">{{ ___('permissions.bookings') }}</span> </a> </li>
            <li> <a href="{{ route('cust.tours') }}" class="{{ request()->routeIs('cust.tours') ? 'mm-active' : '' }}"> <i class="icon-map"></i> <span class="nav-text">{{ ___('menus.tours') }}</span> </a> </li>
            <li> <a href="{{ route('cust.visa') }}" class="{{ request()->routeIs('cust.visa') ? 'mm-active' : '' }}"> <i class="icon-doc"></i> <span class="nav-text">{{ ___('menus.visa_applications') }}</span> </a> </li>
            <li> <a href="{{ route('cust.flights') }}" class="{{ request()->routeIs('cust.flights') ? 'mm-active' : '' }}"> <i class="icon-plane"></i> <span class="nav-text">{{ ___('menus.flight_tickets') }}</span> </a> </li>
            <li> <a href="{{ route('cust.hotels') }}" class="{{ request()->routeIs('cust.hotels') ? 'mm-active' : '' }}"> <i class="icon-home"></i> <span class="nav-text">{{ ___('label.hotel_bookings') }}</span> </a> </li>
            <li> <a href="{{ route('cust.transport') }}" class="{{ request()->routeIs('cust.transport') ? 'mm-active' : '' }}"> <i class="icon-location-pin"></i> <span class="nav-text">{{ ___('label.transport_bookings') }}</span> </a> </li>
            <li> <a href="{{ route('cust.wishlist') }}" class="{{ request()->routeIs('cust.wishlist') ? 'mm-active' : '' }}"> <i class="icon-heart"></i> <span class="nav-text">{{ ___('menus.wishlist') }}</span> </a> </li>
            <li> <a href="{{ route('cust.reviews') }}" class="{{ request()->routeIs('cust.reviews') ? 'mm-active' : '' }}"> <i class="icon-star"></i> <span class="nav-text">My Reviews</span> </a> </li>
            <li> <a href="{{ route('cust.documents') }}" class="{{ request()->routeIs('cust.documents') ? 'mm-active' : '' }}"> <i class="icon-folder"></i> <span class="nav-text">{{ ___('menus.documents') }}</span> </a> </li>

            <li class="nav-label">{{ ___('menus.billing') }}</li>

            <li> <a href="{{ route('cust.invoices') }}" class="{{ request()->routeIs('cust.invoices') ? 'mm-active' : '' }}"> <i class="icon-notebook"></i> <span class="nav-text">{{ ___('menus.manage_invoices') }}</span> </a> </li>
            <li> <a href="{{ route('cust.payments') }}" class="{{ request()->routeIs('cust.payments') ? 'mm-active' : '' }}"> <i class="icon-wallet"></i> <span class="nav-text">{{ ___('menus.payments') }}</span> </a> </li>
            <li> <a href="{{ route('cust.wallet') }}" class="{{ request()->routeIs('cust.wallet') ? 'mm-active' : '' }}"> <i class="icon-credit-card"></i> <span class="nav-text">{{ ___('menus.wallet') }}</span> </a> </li>

            <li class="nav-label">{{ ___('menus.more') }}</li>

            <li> <a href="{{ route('cust.support') }}" class="{{ request()->routeIs('cust.support') ? 'mm-active' : '' }}"> <i class="icon-support"></i> <span class="nav-text">{{ ___('menus.support_tickets') }}</span> </a> </li>
            <li> <a href="{{ route('cust.notifications') }}" class="{{ request()->routeIs('cust.notifications') ? 'mm-active' : '' }}"> <i class="icon-bell"></i> <span class="nav-text">{{ ___('menus.notifications') }}</span> </a> </li>
            <li> <a href="{{ route('cust.settings') }}" class="{{ request()->routeIs('cust.settings') ? 'mm-active' : '' }}"> <i class="icon-settings"></i> <span class="nav-text">{{ ___('menus.preferences') }}</span> </a> </li>

        </ul>
    </div>
</aside>
