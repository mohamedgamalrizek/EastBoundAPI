<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Branch\BranchInterface;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;

class BranchController extends BaseCrudController
{
    protected string $viewPath      = 'backend.branch';
    protected string $redirectRoute = 'branch.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Branch', 'group' => 'status', 'label' => 'Branches'];

    public function __construct(BranchInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreBranchRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateBranchRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
