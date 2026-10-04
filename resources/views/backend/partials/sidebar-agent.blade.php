<aside class="tv-sidebar j-custom-style" id="backendSidebar" aria-label="{{ ___('menus.agent_portal') }}">
    <div class="tv-sidebar-scroll">
        <ul class="tv-sidebar-menu" id="backendSidebarMenu">

            <li class="nav-label first">{{ ___('menus.agent_portal') }}</li>

            <li> <a href="{{ route('agent.dashboard') }}" class="{{ request()->routeIs('agent.dashboard') ? 'mm-active' : '' }}"> <i class="icon-speedometer"></i> <span class="nav-text">{{ ___('menus.dashboard') }}</span> </a> </li>
            {{-- New Booking: the services the agent can sell. Entries appear
                 only once that service can record who sold it, so the menu
                 never offers something that won't attribute the sale. --}}
            <li class="{{ request()->routeIs('agent.booking.create', 'agent.hotel.create', 'agent.transport.create', 'agent.flight.create') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="true"> <i class="icon-handbag"></i> <span class="nav-text">{{ ___('menus.new_booking') }}</span> </a>
                <ul aria-expanded="false">
                    <li> <a href="{{ route('agent.booking.create') }}" class="{{ request()->routeIs('agent.booking.create') ? 'mm-active' : '' }}">{{ ___('menus.tour_package') }}</a> </li>
                    <li> <a href="{{ route('agent.hotel.create') }}" class="{{ request()->routeIs('agent.hotel.create') ? 'mm-active' : '' }}">{{ ___('menus.hotel') }}</a> </li>
                    <li> <a href="{{ route('agent.transport.create') }}" class="{{ request()->routeIs('agent.transport.create') ? 'mm-active' : '' }}">{{ ___('menus.transport') }}</a> </li>
                    <li> <a href="{{ route('agent.flight.create') }}" class="{{ request()->routeIs('agent.flight.create') ? 'mm-active' : '' }}">{{ ___('menus.flight') }}</a> </li>
                </ul>
            </li>
            <li> <a href="{{ route('agent.bookings') }}" class="{{ request()->routeIs('agent.bookings', 'agent.booking.show') ? 'mm-active' : '' }}"> <i class="icon-docs"></i> <span class="nav-text">{{ ___('menus.my_bookings') }}</span> </a> </li>
            <li> <a href="{{ route('agent.customers') }}" class="{{ request()->routeIs('agent.customers') ? 'mm-active' : '' }}"> <i class="icon-people"></i> <span class="nav-text">{{ ___('menus.customers') }}</span> </a> </li>

            <li class="nav-label">{{ ___('menus.earnings') }}</li>

            <li> <a href="{{ route('agent.commissions') }}" class="{{ request()->routeIs('agent.commissions') ? 'mm-active' : '' }}"> <i class="icon-badge"></i> <span class="nav-text">{{ ___('menus.commissions') }}</span> </a> </li>
            <li> <a href="{{ route('agent.wallet') }}" class="{{ request()->routeIs('agent.wallet') ? 'mm-active' : '' }}"> <i class="icon-wallet"></i> <span class="nav-text">{{ ___('menus.wallet') }}</span> </a> </li>
            <li> <a href="{{ route('agent.transactions') }}" class="{{ request()->routeIs('agent.transactions') ? 'mm-active' : '' }}"> <i class="icon-refresh"></i> <span class="nav-text">{{ ___('menus.transactions') }}</span> </a> </li>
            <li> <a href="{{ route('agent.invoices') }}" class="{{ request()->routeIs('agent.invoices') ? 'mm-active' : '' }}"> <i class="icon-notebook"></i> <span class="nav-text">{{ ___('menus.manage_invoices') }}</span> </a> </li>

            <li class="nav-label">{{ ___('menus.more') }}</li>

            <li> <a href="{{ route('agent.reports') }}" class="{{ request()->routeIs('agent.reports') ? 'mm-active' : '' }}"> <i class="icon-graph"></i> <span class="nav-text">{{ ___('menus.reports') }}</span> </a> </li>
            <li> <a href="{{ route('agent.support') }}" class="{{ request()->routeIs('agent.support') ? 'mm-active' : '' }}"> <i class="icon-support"></i> <span class="nav-text">{{ ___('menus.support') }}</span> </a> </li>

        </ul>
    </div>
</aside>
