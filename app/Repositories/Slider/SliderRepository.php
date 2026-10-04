<?php

namespace App\Repositories\Slider;

use App\Models\Slider;
use App\Repositories\BaseRepository;
use App\Repositories\Concerns\StoresPublicImage;
use App\Repositories\Slider\SliderInterface;

class SliderRepository extends BaseRepository implements SliderInterface
{
    use StoresPublicImage;

    /** Allowed option sets — mirror the sliders migration. */
    public const STATUSES = ['active', 'inactive'];

    /** Relations eager-loaded on list / find. */
    protected array $with = [];

    public function __construct(Slider $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'title'       => $request->title,
            'subtitle'    => $request->subtitle,
            'image_label' => $request->image_label,
            'image'       => $this->resolveImage($request, 'image', 'image_url', 'sliders', $this->currentImage($request, 'image')),
            'badge'       => $request->badge,
            'cta_text'    => $request->cta_text,
            'cta_link'    => $request->cta_link,
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
                    ->orWhere('subtitle', 'like', "%{$search}%");
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
