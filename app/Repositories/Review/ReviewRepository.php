<?php

namespace App\Repositories\Review;

use App\Models\Review;
use App\Repositories\BaseRepository;

/**
 * Review moderation.
 *
 * There is deliberately no create(): a review is written by a customer
 * against a booking they paid for, which is what makes it worth reading. The
 * office approves, rejects, answers or removes one — it does not author one.
 */
class ReviewRepository extends BaseRepository implements ReviewInterface
{
    public const STATUS = Review::STATUSES;

    protected array $with = ['customer', 'package', 'booking'];

    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'status' => $request->status,
            'reply'  => $request->reply ?: null,
            // Stamped only when there is something to stamp, so clearing a
            // reply clears its date with it.
            'replied_at' => $request->reply ? now() : null,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('package', fn ($p) => $p->where('title', 'like', "%{$search}%"));
            });
        }
    }

    public function setStatus($id, string $status)
    {
        try {
            if (! in_array($status, Review::STATUSES, true)) {
                return $this->responseWithError(___('alert.something_went_wrong'));
            }

            $review = $this->find($id);
            $review->update(['status' => $status]);
            $this->logActivity('updated', $review);

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function formData(): array
    {
        return ['status_options' => Review::STATUSES];
    }
}
