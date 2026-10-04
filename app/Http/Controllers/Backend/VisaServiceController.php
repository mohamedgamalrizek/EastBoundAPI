<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\VisaService\VisaServiceInterface;
use App\Http\Requests\VisaService\StoreVisaServiceRequest;
use App\Http\Requests\VisaService\UpdateVisaServiceRequest;

/**
 * Manages the visa catalogue advertised on the public site
 * (/visa-services). Customer applications live in VisaController.
 */
class VisaServiceController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.visa-service';
    protected string $redirectRoute = 'cms.visa-service.index';

    protected ?array $listAnalyticsConfig = ['model' => 'VisaService', 'group' => 'visa_type', 'label' => 'Visa services'];

    public function __construct(VisaServiceInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreVisaServiceRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateVisaServiceRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
