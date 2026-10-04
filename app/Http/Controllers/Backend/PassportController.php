<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Passport\PassportInterface;
use App\Http\Requests\Passport\StorePassportRequest;
use App\Http\Requests\Passport\UpdatePassportRequest;

class PassportController extends BaseCrudController
{
    protected string $viewPath      = 'backend.customer.passport';
    protected string $redirectRoute = 'customer.passport.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Passport', 'group' => 'status', 'label' => 'Passports'];

    public function __construct(PassportInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StorePassportRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdatePassportRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
