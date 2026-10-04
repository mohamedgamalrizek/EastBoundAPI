<?php

namespace App\Repositories\KbArticle;

use App\Models\KbArticle;
use App\Repositories\BaseRepository;
use App\Repositories\KbArticle\KbArticleInterface;

class KbArticleRepository extends BaseRepository implements KbArticleInterface
{
    /** Allowed option sets — mirror the kb_articles migration. */
    public const STATUSES = ['published', 'draft'];

    /** Relations eager-loaded on list / find. */
    protected array $with = [];

    public function __construct(KbArticle $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'title'    => $request->title,
            'category' => $request->category,
            'excerpt'  => $request->excerpt,
            'status'   => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
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
