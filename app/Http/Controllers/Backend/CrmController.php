<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Crm\CrmInterface;

class CrmController extends Controller
{
    protected $repo;

    public function __construct(CrmInterface $repo)
    {
        $this->repo = $repo;
    }

    // Leads are now a real DB-backed module — see LeadController.

    public function followup()
    {
        return view('backend.crm.followup-calendar', $this->repo->followup());
    }

    public function activity()
    {
        return view('backend.crm.activity-timeline', $this->repo->activity());
    }

    public function notes()
    {
        return view('backend.crm.notes', $this->repo->notes());
    }

    public function communication()
    {
        return view('backend.crm.communication', $this->repo->communication());
    }
}
