<aside class="tv-sidebar j-custom-style" id="backendSidebar" aria-label="{{ ___('menus.platform') }}">
    <div class="tv-sidebar-scroll">
        <ul class="tv-sidebar-menu" id="backendSidebarMenu">

            <li class="nav-label first">{{ ___('menus.platform') }}</li>

            <li> <a href="{{ route('saas.dashboard') }}" class="{{ request()->routeIs('saas.dashboard') ? 'mm-active' : '' }}"> <i class="icon-speedometer"></i> <span class="nav-text">{{ ___('menus.dashboard') }}</span> </a> </li>
            <li> <a href="{{ route('saas.tenants') }}" class="{{ request()->routeIs('saas.tenants') ? 'mm-active' : '' }}"> <i class="icon-people"></i> <span class="nav-text">{{ ___('menus.tenants') }}</span> </a> </li>
            <li> <a href="{{ route('saas.domains') }}" class="{{ request()->routeIs('saas.domains') ? 'mm-active' : '' }}"> <i class="icon-globe"></i> <span class="nav-text">{{ ___('menus.domains') }}</span> </a> </li>

            <li class="nav-label">{{ ___('menus.billing') }}</li>

            <li> <a href="{{ route('saas.plans') }}" class="{{ request()->routeIs('saas.plans') ? 'mm-active' : '' }}"> <i class="icon-layers"></i> <span class="nav-text">{{ ___('menus.plans') }}</span> </a> </li>
            <li> <a href="{{ route('saas.subscriptions') }}" class="{{ request()->routeIs('saas.subscriptions') ? 'mm-active' : '' }}"> <i class="icon-refresh"></i> <span class="nav-text">{{ ___('menus.subscriptions') }}</span> </a> </li>
            <li> <a href="{{ route('saas.invoices') }}" class="{{ request()->routeIs('saas.invoices') ? 'mm-active' : '' }}"> <i class="icon-notebook"></i> <span class="nav-text">{{ ___('menus.manage_invoices') }}</span> </a> </li>
            <li> <a href="{{ route('saas.payments') }}" class="{{ request()->routeIs('saas.payments') ? 'mm-active' : '' }}"> <i class="icon-wallet"></i> <span class="nav-text">{{ ___('menus.payments') }}</span> </a> </li>
            <li> <a href="{{ route('saas.gateways') }}" class="{{ request()->routeIs('saas.gateways') ? 'mm-active' : '' }}"> <i class="icon-credit-card"></i> <span class="nav-text">{{ ___('menus.payment_gateways') }}</span> </a> </li>
            <li> <a href="{{ route('saas.payment.records') }}" class="{{ request()->routeIs('saas.payment.records') ? 'mm-active' : '' }}"> <i class="icon-list"></i> <span class="nav-text">{{ ___('menus.payment_records') }}</span> </a> </li>

            <li class="nav-label">{{ ___('menus.platform_admin') }}</li>

            <li> <a href="{{ route('saas.features') }}" class="{{ request()->routeIs('saas.features') ? 'mm-active' : '' }}"> <i class="icon-grid"></i> <span class="nav-text">{{ ___('menus.feature_management') }}</span> </a> </li>
            <li> <a href="{{ route('saas.settings') }}" class="{{ request()->routeIs('saas.settings') ? 'mm-active' : '' }}"> <i class="icon-settings"></i> <span class="nav-text">{{ ___('menus.system_settings') }}</span> </a> </li>
            <li> <a href="{{ route('saas.audit') }}" class="{{ request()->routeIs('saas.audit') ? 'mm-active' : '' }}"> <i class="icon-list"></i> <span class="nav-text">{{ ___('menus.audit_logs') }}</span> </a> </li>
            <li> <a href="{{ route('saas.analytics') }}" class="{{ request()->routeIs('saas.analytics') ? 'mm-active' : '' }}"> <i class="icon-graph"></i> <span class="nav-text">{{ ___('menus.analytics') }}</span> </a> </li>
            <li> <a href="{{ route('saas.support') }}" class="{{ request()->routeIs('saas.support') ? 'mm-active' : '' }}"> <i class="icon-support"></i> <span class="nav-text">{{ ___('menus.support') }}</span> </a> </li>

        </ul>
    </div>
</aside>
