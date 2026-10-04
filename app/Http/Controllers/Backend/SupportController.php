<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Support\SupportInterface;
use App\Http\Requests\Support\StoreSupportRequest;
use App\Http\Requests\Support\UpdateSupportRequest;

class SupportController extends BaseCrudController
{
    protected string $viewPath      = 'backend.support';
    protected string $redirectRoute = 'support.index';

    protected ?array $listAnalyticsConfig = ['model' => 'SupportTicket', 'group' => 'status', 'label' => 'Tickets'];

    public function __construct(SupportInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreSupportRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateSupportRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function ticket($id)
    {
        return view('backend.support.ticket', $this->repo->ticket($id));
    }

    public function kb()
    {
        return view('backend.support.kb', $this->repo->kb());
    }

}
