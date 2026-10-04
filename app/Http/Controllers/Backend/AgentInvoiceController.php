<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\AgentInvoice\AgentInvoiceInterface;
use App\Http\Requests\AgentInvoice\StoreAgentInvoiceRequest;
use App\Http\Requests\AgentInvoice\UpdateAgentInvoiceRequest;

class AgentInvoiceController extends BaseCrudController
{
    protected string $viewPath      = 'backend.agent.invoice';
    protected string $redirectRoute = 'agent.invoice.index';

    protected ?array $listAnalyticsConfig = ['model' => 'AgentInvoice', 'group' => 'status', 'sum' => 'amount', 'label' => 'Invoices', 'sumLabel' => 'Total Billed'];

    public function __construct(AgentInvoiceInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreAgentInvoiceRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateAgentInvoiceRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
