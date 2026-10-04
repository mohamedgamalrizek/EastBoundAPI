<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\AccountTransaction\AccountTransactionInterface;
use App\Http\Requests\AccountTransaction\StoreAccountTransactionRequest;
use App\Http\Requests\AccountTransaction\UpdateAccountTransactionRequest;

class AccountTransactionController extends BaseCrudController
{
    protected string $viewPath      = 'backend.accounting.transaction';
    protected string $redirectRoute = 'acc.txn.index';

    protected ?array $listAnalyticsConfig = ['model' => 'AccountTransaction', 'group' => 'type', 'sum' => 'amount', 'label' => 'Transactions', 'sumLabel' => 'Total Amount'];

    public function __construct(AccountTransactionInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreAccountTransactionRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateAccountTransactionRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
