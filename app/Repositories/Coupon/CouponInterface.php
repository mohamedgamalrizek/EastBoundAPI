<?php

namespace App\Repositories\Coupon;

interface CouponInterface
{
    public function all(array $filters = []);
    public function find($id);
    public function formData();
    public function store($request);
    public function update($request);
    public function delete($id);
}
