<?php

namespace App\Repositories\Faq;

use App\Models\Faq;
use App\Repositories\BaseRepository;
use App\Repositories\Faq\FaqInterface;

class FaqRepository extends BaseRepository implements FaqInterface
{
    /** Allowed option sets — mirror the faqs migration. */
    public const STATUSES = ['active', 'inactive'];

    /** Relations eager-loaded on list / find. */
    protected array $with = [];

    public function __construct(Faq $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'question' => $request->question,
            'answer'   => $request->answer,
            'category' => $request->category,
            'status'   => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%");
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
