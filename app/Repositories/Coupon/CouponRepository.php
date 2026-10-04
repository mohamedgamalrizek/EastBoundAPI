<?php

namespace App\Repositories\Coupon;

use App\Models\Coupon;
use App\Repositories\BaseRepository;
use App\Repositories\Coupon\CouponInterface;

class CouponRepository extends BaseRepository implements CouponInterface
{
    public const TYPE = ['Percentage', 'Fixed'];
    public const STATUS = ['active', 'inactive'];

    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'min_spend' => $request->min_spend,
            'usage_limit' => $request->usage_limit,
            'expires_at' => $request->expires_at,
            'description' => $request->description,
            'status' => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'type_options' => self::TYPE,
            'status_options' => self::STATUS,
        ];
    }
}
