<?php

namespace App\Repositories\AccountTransaction;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Repositories\BaseRepository;
use App\Repositories\AccountTransaction\AccountTransactionInterface;

/**
 * Manual journal entries.
 *
 * Every entry names two accounts — the account being classified and the contra
 * account the money moved through — so it can be posted as a debit and a
 * credit. Entries written automatically from an invoice, receipt or payslip are
 * read-only here: they belong to their document, and editing them by hand would
 * put the books out of step with it.
 */
class AccountTransactionRepository extends BaseRepository implements AccountTransactionInterface
{
    /** Allowed option sets — mirror AccountTransaction::TYPES. */
    public const TYPES = AccountTransaction::TYPES;

    /** Relations eager-loaded on list / find. */
    protected array $with = ['account', 'contraAccount'];

    public function __construct(AccountTransaction $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'account_id'        => $request->account_id,
            'contra_account_id' => $request->contra_account_id,
            'txn_date'          => $request->txn_date,
            // Kept as a snapshot of the account's name at posting time.
            'account_name'      => $request->account_name
                                    ?: Account::whereKey($request->account_id)->value('name'),
            'type'              => $request->type,
            'amount'            => $request->amount,
            'reference'         => $request->reference,
            'description'       => $request->description,
        ];
    }

    /** Auto-posted entries are maintained by their source document. */
    public function update($request)
    {
        $entry = $this->find($request->id);

        if ($entry->isAutoPosted()) {
            return $this->responseWithError(
                ___('alert.something_went_wrong') . ' — ' .
                'this entry was posted automatically from a ' . strtolower($entry->sourceLabel()) . '; edit that record instead.'
            );
        }

        return parent::update($request);
    }

    protected function guardDelete($model): ?string
    {
        if ($model->isAutoPosted()) {
            return 'This entry belongs to a ' . strtolower($model->sourceLabel()) . ' — delete that record instead.';
        }

        return null;
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['account_id'])) {
            $query->touchingAccounts([(int) $filters['account_id']]);
        }

        $query->between($filters['from'] ?? null, $filters['to'] ?? null);
    }

    public function formData(): array
    {
        return [
            'accounts' => Account::orderBy('code')->get(['id', 'code', 'name', 'type']),
            'types'    => self::TYPES,
        ];
    }
}
