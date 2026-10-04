<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\MedicalTour\MedicalTourInterface;
use App\Http\Requests\MedicalTour\StoreMedicalTourRequest;
use App\Http\Requests\MedicalTour\UpdateMedicalTourRequest;

class MedicalTourController extends BaseCrudController
{
    protected string $viewPath      = 'backend.medical-tour';
    protected string $redirectRoute = 'medical-tour.index';

    protected ?array $listAnalyticsConfig = ['model' => 'MedicalTour', 'group' => 'destination', 'sum' => 'cost', 'label' => 'Cases', 'sumLabel' => 'Total Cost'];

    public function __construct(MedicalTourInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreMedicalTourRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateMedicalTourRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
