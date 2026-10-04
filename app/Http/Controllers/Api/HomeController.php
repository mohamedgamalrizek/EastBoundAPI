<?php

namespace App\Http\Controllers\Api;

use App\Models\Slider;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;

/**
 * Home screen feed for the FLOW app: banners, tour categories and
 * featured / latest tours. Public (no auth required to browse).
 */
class HomeController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait;

    public function index()
    {
        $sliders = Slider::where('status', 'active')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Slider $s) => [
                'id'        => $s->id,
                'title'     => $s->title,
                'subtitle'  => $s->subtitle,
                // `image_label` is the admin's caption for the upload, not a
                // path — reading it here made every slide fall through to the
                // placeholder. The website reads `image`; so does this.
                'image_url' => $this->imageUrl($s->image, 'slider' . $s->id),
            ]);

        $categories = PackageCategory::where('status', 'active')
            ->get()
            ->map(fn (PackageCategory $c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ]);

        $latest = Package::where('status', 'active')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Package $p) => $this->tourCard($p));

        // Featured = most recently created active tours (top 6) for the hero rail.
        $featured = $latest->take(6)->values();

        return $this->responseWithSuccess('Home loaded.', [
            'sliders'        => $sliders,
            'categories'     => $categories,
            'featured_tours' => $featured,
            'latest_tours'   => $latest,
        ]);
    }
}
