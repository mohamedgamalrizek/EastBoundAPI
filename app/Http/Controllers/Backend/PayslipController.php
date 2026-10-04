<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Payslip\PayslipInterface;
use App\Http\Requests\Payslip\StorePayslipRequest;
use App\Http\Requests\Payslip\UpdatePayslipRequest;

class PayslipController extends BaseCrudController
{
    protected string $viewPath      = 'backend.hr.payslip';
    protected string $redirectRoute = 'hr.payslip.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Payslip', 'group' => 'status', 'sum' => 'net_pay', 'label' => 'Payslips', 'sumLabel' => 'Total Net Pay'];

    public function __construct(PayslipInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StorePayslipRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdatePayslipRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
