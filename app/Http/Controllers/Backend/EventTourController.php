<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\EventTour\EventTourInterface;
use App\Http\Requests\EventTour\StoreEventTourRequest;
use App\Http\Requests\EventTour\UpdateEventTourRequest;

class EventTourController extends BaseCrudController
{
    protected string $viewPath      = 'backend.event-tour';
    protected string $redirectRoute = 'event-tour.index';

    protected ?array $listAnalyticsConfig = ['model' => 'EventTour', 'group' => 'type', 'label' => 'Events'];

    public function __construct(EventTourInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreEventTourRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateEventTourRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
