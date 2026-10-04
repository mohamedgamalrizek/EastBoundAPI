<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\TransportService\TransportServiceInterface;
use App\Http\Requests\TransportService\StoreTransportServiceRequest;
use App\Http\Requests\TransportService\UpdateTransportServiceRequest;

/**
 * Manages the transport products advertised on /transport-booking.
 * Actual transport jobs are handled by TransportController.
 */
class TransportServiceController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.transport-service';
    protected string $redirectRoute = 'cms.transport-service.index';

    public function __construct(TransportServiceInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreTransportServiceRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateTransportServiceRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
