<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Insurance\InsuranceInterface;
use App\Http\Requests\Insurance\StoreInsuranceRequest;
use App\Http\Requests\Insurance\UpdateInsuranceRequest;

class InsuranceController extends BaseCrudController
{
    protected string $viewPath      = 'backend.insurance';
    protected string $redirectRoute = 'insurance.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Insurance', 'group' => 'type', 'sum' => 'premium', 'label' => 'Policies', 'sumLabel' => 'Total Premium'];

    public function __construct(InsuranceInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreInsuranceRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateInsuranceRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
