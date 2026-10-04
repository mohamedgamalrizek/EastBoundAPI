<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Traveler\TravelerInterface;
use App\Http\Requests\Traveler\StoreTravelerRequest;
use App\Http\Requests\Traveler\UpdateTravelerRequest;

class TravelerController extends BaseCrudController
{
    protected string $viewPath      = 'backend.customer.traveler';
    protected string $redirectRoute = 'customer.traveler.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Traveler', 'group' => 'status', 'label' => 'Travelers'];

    public function __construct(TravelerInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreTravelerRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateTravelerRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
