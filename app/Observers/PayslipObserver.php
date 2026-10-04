<?php

namespace App\Observers;

use App\Models\Payslip;
use App\Services\Accounting\LedgerService;

/**
 * Payroll reaches the books when a payslip is marked Paid: Dr Salaries, Cr
 * Cash. Flipping it back off Paid reverses the entry (postPayslip unposts).
 */
class PayslipObserver
{
    public function __construct(protected LedgerService $ledger) {}

    public function saved(Payslip $payslip): void
    {
        $this->ledger->postPayslip($payslip);
    }

    public function deleted(Payslip $payslip): void
    {
        $this->ledger->unpostSource($payslip);
    }
}
