<?php

namespace App\Repositories\Blog;

use App\Models\Blog;
use App\Repositories\BaseRepository;
use App\Repositories\Concerns\StoresPublicImage;
use App\Repositories\Blog\BlogInterface;

class BlogRepository extends BaseRepository implements BlogInterface
{
    use StoresPublicImage;

    /** Allowed option sets — mirror the blogs migration. */
    public const STATUSES = ['published', 'draft'];

    protected array $with = [];

    public function __construct(Blog $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'title'        => $request->title,
            'slug'         => $request->slug,
            'author'       => $request->author,
            'category'     => $request->category,
            'image'       => $this->resolveImage($request, 'image', 'image_url', 'blog', $this->currentImage($request, 'image')),
            'excerpt'      => $request->excerpt,
            'body'         => $request->body,
            'read_minutes' => $request->read_minutes ?: 4,
            'status'       => $request->status,
            'published_at' => $request->published_at,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
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
