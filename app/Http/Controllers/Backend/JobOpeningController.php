<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\JobOpening\JobOpeningInterface;
use App\Http\Requests\JobOpening\StoreJobOpeningRequest;
use App\Http\Requests\JobOpening\UpdateJobOpeningRequest;

/**
 * Manages the vacancies listed on the public careers page.
 */
class JobOpeningController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.job-opening';
    protected string $redirectRoute = 'cms.job-opening.index';

    // Grouped by employment_type (never null) rather than the nullable department.
    protected ?array $listAnalyticsConfig = ['model' => 'JobOpening', 'group' => 'employment_type', 'label' => 'Vacancies'];

    public function __construct(JobOpeningInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreJobOpeningRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateJobOpeningRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
