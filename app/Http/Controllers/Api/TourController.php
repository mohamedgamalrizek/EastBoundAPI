<?php

namespace App\Http\Controllers\Api;

use App\Models\Package;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;
use Illuminate\Http\Request;

/**
 * Tour packages browsing for the app (Explore tab).
 * Public — anyone can browse published tours; booking requires auth.
 */
class TourController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait;

    /**
     * Paginated, filterable list of active tours.
     * Filters: category_id, search, min_price, max_price, sort.
     */
    public function index(Request $request)
    {
        $query = Package::where('status', 'active');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('destination', 'like', "%{$term}%"));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        switch ($request->get('sort')) {
            case 'price_low':  $query->orderBy('price');         break;
            case 'price_high': $query->orderByDesc('price');     break;
            default:           $query->latest();                 break;
        }

        $paginator = $query->paginate(min((int) $request->get('per_page', 10), 50));

        return $this->responseWithSuccess('Tours fetched.', [
            'tours' => collect($paginator->items())->map(fn (Package $p) => $this->tourCard($p)),
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * Single tour detail with category + upcoming schedules.
     */
    public function show($id)
    {
        $package = Package::where('status', 'active')->find($id);

        if (! $package) {
            return $this->responseWithError('Tour not found.', [], 404);
        }

        return $this->responseWithSuccess('Tour detail fetched.', [
            'tour'    => $this->tourDetail($package),
            'related' => $this->relatedTours($package),
        ]);
    }

    /**
     * "You may also like" — same category or destination first, falling back to
     * the newest other tours, exactly as the website's package page does.
     */
    private function relatedTours(Package $package)
    {
        $related = Package::where('status', 'active')
            ->where('id', '!=', $package->id)
            ->where(function ($w) use ($package) {
                $w->where('category', $package->category)
                  ->orWhere('destination', $package->destination);
            })
            ->latest()->take(4)->get();

        if ($related->isEmpty()) {
            $related = Package::where('status', 'active')
                ->where('id', '!=', $package->id)
                ->latest()->take(4)->get();
        }

        return $related->map(fn (Package $p) => $this->tourCard($p))->values();
    }
}
