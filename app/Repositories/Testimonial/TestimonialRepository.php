<?php

namespace App\Repositories\Testimonial;

use App\Models\Testimonial;
use App\Repositories\BaseRepository;
use App\Repositories\Concerns\StoresPublicImage;
use App\Repositories\Testimonial\TestimonialInterface;

class TestimonialRepository extends BaseRepository implements TestimonialInterface
{
    use StoresPublicImage;

    /** Allowed option sets — mirror the testimonials migration. */
    public const STATUSES = ['active', 'inactive'];

    /** Relations eager-loaded on list / find. */
    protected array $with = [];

    public function __construct(Testimonial $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'name'    => $request->name,
            'role'    => $request->role,
            'avatar'      => $this->resolveImage($request, 'avatar', 'avatar_url', 'testimonials', $this->currentImage($request, 'avatar')),
            'city'    => $request->city,
            'message' => $request->message,
            'rating'  => $request->rating,
            'status'  => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
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
