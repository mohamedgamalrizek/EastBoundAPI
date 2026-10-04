<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\CrmActivity\CrmActivityInterface;
use App\Http\Requests\CrmActivity\StoreCrmActivityRequest;
use App\Http\Requests\CrmActivity\UpdateCrmActivityRequest;

class CrmActivityController extends BaseCrudController
{
    protected string $viewPath      = 'backend.crm.activity';
    protected string $redirectRoute = 'crm.activity.index';

    protected ?array $listAnalyticsConfig = ['model' => 'CrmActivity', 'group' => 'type', 'label' => 'Activities'];

    public function __construct(CrmActivityInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreCrmActivityRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateCrmActivityRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
