<?php

namespace App\View\Composers;

use App\Models\Menu;
use Illuminate\View\View;

/**
 * Supplies the CMS-managed navigation to the public header and footer.
 *
 * Deliberately does not memoise: a cache held on the composer would outlive
 * the request under a long-lived worker (Octane) and serve a stale menu after
 * an admin edit. The trees are two eager-loaded queries each, so re-reading
 * them per partial is cheap.
 */
class NavigationComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'headerMenu'      => Menu::tree(Menu::POSITION_HEADER),
            'footerMenu'      => Menu::tree(Menu::POSITION_FOOTER),
            'footerLegalMenu' => Menu::tree(Menu::POSITION_FOOTER_LEGAL),
        ]);
    }
}
