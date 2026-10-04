<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\VehicleCategory\VehicleCategoryInterface;
use App\Http\Requests\VehicleCategory\StoreVehicleCategoryRequest;
use App\Http\Requests\VehicleCategory\UpdateVehicleCategoryRequest;

/**
 * The vehicle categories offered on the airport transfer / car rental forms
 * and the admin transport booking form's Vehicle field. A sub-entity of
 * Transport Management, reusing its transport_* permissions.
 */
class VehicleCategoryController extends BaseCrudController
{
    protected string $viewPath      = 'backend.vehicle-category';
    protected string $redirectRoute = 'transport.vehicle-category.index';

    public function __construct(VehicleCategoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreVehicleCategoryRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateVehicleCategoryRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
