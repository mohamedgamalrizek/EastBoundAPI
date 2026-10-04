<?php

namespace App\Repositories\Gallery;

use App\Models\Gallery;
use App\Repositories\BaseRepository;
use App\Repositories\Concerns\StoresPublicImage;
use App\Repositories\Gallery\GalleryInterface;

class GalleryRepository extends BaseRepository implements GalleryInterface
{
    use StoresPublicImage;

    /** Allowed option sets — mirror the galleries migration. */
    public const STATUSES = ['active', 'inactive'];

    /** Relations eager-loaded on list / find. */
    protected array $with = [];

    public function __construct(Gallery $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'title'       => $request->title,
            'image_label' => $request->image_label,
            'image'       => $this->resolveImage($request, 'image', 'image_url', 'gallery', $this->currentImage($request, 'image')),
            'category'    => $request->category,
            'sort_order'  => $request->sort_order ?? 0,
            'status'      => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'statuses' => self::STATUSES,
        ];
    }
}
