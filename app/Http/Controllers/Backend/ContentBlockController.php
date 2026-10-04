<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\ContentBlock\ContentBlockInterface;
use App\Http\Requests\ContentBlock\StoreContentBlockRequest;
use App\Http\Requests\ContentBlock\UpdateContentBlockRequest;

/**
 * Manages the reusable icon cards across the public site (home grids, about
 * values, visa steps, support topics, agent benefits, pilgrimage inclusions).
 */
class ContentBlockController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.content-block';
    protected string $redirectRoute = 'cms.content-block.index';

    public function __construct(ContentBlockInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreContentBlockRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateContentBlockRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
