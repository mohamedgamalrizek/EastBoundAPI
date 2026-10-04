<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\HajjPilgrim\HajjPilgrimInterface;
use App\Http\Requests\HajjPilgrim\StoreHajjPilgrimRequest;
use App\Http\Requests\HajjPilgrim\UpdateHajjPilgrimRequest;

class HajjPilgrimController extends BaseCrudController
{
    protected string $viewPath      = 'backend.hajj.pilgrim';
    protected string $redirectRoute = 'hajj.pilgrim.index';

    protected ?array $listAnalyticsConfig = ['model' => 'HajjPilgrim', 'group' => 'status', 'label' => 'Pilgrims'];

    public function __construct(HajjPilgrimInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreHajjPilgrimRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateHajjPilgrimRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
