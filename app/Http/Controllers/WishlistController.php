<?php

namespace App\Http\Controllers;

use App\Repositories\CustomerPortal\CustomerPortalInterface;
use Illuminate\Http\Request;

/**
 * The heart icon on public package cards. Lives outside the portal group
 * (it's clicked from /tour-packages, not from inside the portal) but shares
 * the portal's customer-identity resolution, so a save here shows up on the
 * customer's My Wishlist page and vice versa.
 */
class WishlistController extends Controller
{
    protected $repo;

    public function __construct(CustomerPortalInterface $repo)
    {
        $this->repo = $repo;
    }

    public function toggle(Request $request, $package)
    {
        $result = $this->repo->toggleWishlist($package);

        if ($request->expectsJson()) {
            return response()->json($result, $result['status_code']);
        }

        return back()->with($result['status'] ? 'success' : 'danger', $result['message']);
    }
}
