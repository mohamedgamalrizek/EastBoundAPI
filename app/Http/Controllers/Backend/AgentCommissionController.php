<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\AgentCommission\AgentCommissionInterface;
use App\Http\Requests\AgentCommission\StoreAgentCommissionRequest;
use App\Http\Requests\AgentCommission\UpdateAgentCommissionRequest;

class AgentCommissionController extends BaseCrudController
{
    protected string $viewPath      = 'backend.agent.commission';
    protected string $redirectRoute = 'agent.commission.index';

    protected ?array $listAnalyticsConfig = ['model' => 'AgentCommission', 'group' => 'status', 'sum' => 'amount', 'label' => 'Commissions', 'sumLabel' => 'Total Commission'];

    public function __construct(AgentCommissionInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreAgentCommissionRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateAgentCommissionRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /** Release the commission into the agent's wallet. */
    public function approve($id)
    {
        return $this->back($this->repo->approve($id));
    }

    public function unapprove($id)
    {
        return $this->back($this->repo->unapprove($id));
    }

    private function back(array $result)
    {
        return redirect()->route($this->redirectRoute)
            ->with($result['status'] ? 'success' : 'danger', $result['message']);
    }
}
