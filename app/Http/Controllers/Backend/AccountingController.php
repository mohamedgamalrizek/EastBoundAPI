<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Repositories\Accounting\AccountingInterface;
use App\Http\Requests\Accounting\StoreAccountingRequest;
use App\Http\Requests\Accounting\UpdateAccountingRequest;

class AccountingController extends BaseCrudController
{
    protected string $viewPath      = 'backend.accounting';
    protected string $redirectRoute = 'acc.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Account', 'group' => 'type', 'sum' => 'balance', 'label' => 'Accounts', 'sumLabel' => 'Total Balance'];

    public function __construct(AccountingInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreAccountingRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateAccountingRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function dashboard(Request $request)
    {
        return view('backend.accounting.dashboard', $this->repo->dashboard($request->query()));
    }

    public function income(Request $request)
    {
        return view('backend.accounting.income', $this->repo->income($request->query()));
    }

    public function expenses(Request $request)
    {
        return view('backend.accounting.expenses', $this->repo->expenses($request->query()));
    }

    public function journal(Request $request)
    {
        return view('backend.accounting.journal', $this->repo->journal($request->query()));
    }

    public function cashbook(Request $request)
    {
        return view('backend.accounting.cashbook', $this->repo->cashbook($request->query()));
    }

    public function bankbook(Request $request)
    {
        return view('backend.accounting.bankbook', $this->repo->bankbook($request->query()));
    }

    public function ledger(Request $request)
    {
        return view('backend.accounting.ledger', $this->repo->ledger($request->query()));
    }

    public function trial(Request $request)
    {
        return view('backend.accounting.trial', $this->repo->trial($request->query()));
    }

    public function pl(Request $request)
    {
        return view('backend.accounting.pl', $this->repo->pl($request->query()));
    }

    public function balance(Request $request)
    {
        return view('backend.accounting.balance', $this->repo->balance($request->query()));
    }

    public function refunds(Request $request)
    {
        return view('backend.accounting.refunds', $this->repo->refunds($request->query()));
    }

    public function tax(Request $request)
    {
        return view('backend.accounting.tax', $this->repo->tax($request->query()));
    }
}
