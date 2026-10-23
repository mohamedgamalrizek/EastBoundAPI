<?php

namespace App\Services\Dmc;

use App\Models\Account;
use App\Models\Dmc\Trip;
use App\Models\Dmc\TripExpense;
use App\Services\Accounting\LedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TripExpenses
{
    public function record(array $data): TripExpense
    {
        return DB::transaction(function () use ($data) {
            Trip::lockForUpdate()->findOrFail($data['trip_id']);
            $existing = TripExpense::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                if ($existing->trip_id != $data['trip_id'] || abs((float) $existing->amount - $data['amount']) > 0.001
                    || $existing->reference !== $data['reference'] || $existing->method !== $data['method']
                    || $existing->expense_account_id != $data['expense_account_id'] || $existing->spent_on->toDateString() !== $data['spent_on']) {
                    throw ValidationException::withMessages(['workflow' => 'This expense identifier is already in use.']);
                }

                return $existing;
            }
            $account = Account::where('type', 'Expense')->findOrFail($data['expense_account_id']);
            $cash = Account::system($data['method'] === 'Cash' ? 'cash' : 'bank');
            if (! $cash) {
                throw ValidationException::withMessages(['workflow' => 'The cash/bank account is missing.']);
            }
            $expense = TripExpense::create($data);
            app(LedgerService::class)->postForSource($expense, [
                'account_id' => $account->id, 'contra_account_id' => $cash->id,
                'txn_date' => $expense->spent_on, 'account_name' => $account->name, 'type' => 'expense',
                'amount' => $expense->amount, 'reference' => $expense->reference,
                'description' => 'Trip #'.$expense->trip_id.': '.$expense->title,
            ]);

            return $expense;
        }, 3);
    }
}
