<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Campaign\CampaignInterface;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;

class CampaignController extends BaseCrudController
{
    protected string $viewPath      = 'backend.campaign';
    protected string $redirectRoute = 'campaign.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Campaign', 'group' => 'status', 'sum' => 'budget', 'label' => 'Campaigns', 'sumLabel' => 'Total Budget'];

    public function __construct(CampaignInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreCampaignRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateCampaignRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
