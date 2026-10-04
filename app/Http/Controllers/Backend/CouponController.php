<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Coupon\CouponInterface;
use App\Http\Requests\Coupon\StoreCouponRequest;
use App\Http\Requests\Coupon\UpdateCouponRequest;

class CouponController extends BaseCrudController
{
    protected string $viewPath      = 'backend.coupon';
    protected string $redirectRoute = 'coupon.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Coupon', 'group' => 'type', 'label' => 'Coupons'];

    public function __construct(CouponInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreCouponRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateCouponRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
