<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\CorporateTravel\CorporateTravelInterface;
use App\Http\Requests\CorporateTravel\StoreCorporateTravelRequest;
use App\Http\Requests\CorporateTravel\UpdateCorporateTravelRequest;

class CorporateTravelController extends BaseCrudController
{
    protected string $viewPath      = 'backend.corporate-travel';
    protected string $redirectRoute = 'corporate-travel.index';

    protected ?array $listAnalyticsConfig = ['model' => 'CorporateTravel', 'group' => 'service_type', 'sum' => 'budget', 'label' => 'Accounts', 'sumLabel' => 'Total Budget'];

    public function __construct(CorporateTravelInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreCorporateTravelRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateCorporateTravelRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
