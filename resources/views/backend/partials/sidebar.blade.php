<aside class="tv-sidebar j-custom-style" id="backendSidebar" aria-label="{{ ___('menus.main') }}">
    <div class="tv-sidebar-scroll">
        <ul class="tv-sidebar-menu" id="backendSidebarMenu">
            @foreach(\App\Support\AdminMenu::sections() as $section)
                <li class="tv-nav-section"><span>{{ $section['label'] }}</span></li>
                @include('backend.partials.sidebar-items', [
                    'items' => $section['items'],
                ])
            @endforeach
        </ul>
    </div>
</aside>
