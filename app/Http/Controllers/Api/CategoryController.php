<?php

namespace App\Http\Controllers\Api;

use App\Models\PackageCategory;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;

class CategoryController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait;

    public function index()
    {
        $categories = PackageCategory::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (PackageCategory $c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ]);

        return $this->responseWithSuccess('Categories fetched.', $categories);
    }
}
