<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Repositories\AgentWithdrawal\AgentWithdrawalInterface;
use App\Http\Requests\AgentWithdrawal\StoreAgentWithdrawalRequest;
use App\Http\Requests\AgentWithdrawal\UpdateAgentWithdrawalRequest;

/**
 * Back-office side of agent payouts: review what agents have asked for,
 * approve it, and mark it paid — which is the point where the wallet is
 * debited and the cash entry hits the books.
 */
class AgentWithdrawalController extends BaseCrudController
{
    protected string $viewPath      = 'backend.agent.withdrawal';
    protected string $redirectRoute = 'agent.withdrawal.index';

    protected ?array $listAnalyticsConfig = ['model' => 'AgentWithdrawal', 'group' => 'status', 'sum' => 'amount', 'label' => 'Payouts', 'sumLabel' => 'Total Requested'];

    public function __construct(AgentWithdrawalInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreAgentWithdrawalRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateAgentWithdrawalRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    public function approve($id)
    {
        return $this->back($this->repo->approve($id));
    }

    public function pay($id)
    {
        return $this->back($this->repo->pay($id));
    }

    public function reject(Request $request, $id)
    {
        return $this->back($this->repo->reject($id, $request->input('note')));
    }

    /** Status actions return to the list they were fired from. */
    private function back(array $result)
    {
        return redirect()->route($this->redirectRoute)
            ->with($result['status'] ? 'success' : 'danger', $result['message']);
    }
}
