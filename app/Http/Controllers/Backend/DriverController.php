<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Driver\DriverInterface;
use App\Http\Requests\Driver\StoreDriverRequest;
use App\Http\Requests\Driver\UpdateDriverRequest;

class DriverController extends BaseCrudController
{
    protected string $viewPath      = 'backend.driver';
    protected string $redirectRoute = 'transport.driver.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Driver', 'group' => 'status', 'label' => 'Drivers'];

    public function __construct(DriverInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreDriverRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateDriverRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
