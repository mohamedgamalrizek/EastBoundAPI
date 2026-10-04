<?php

namespace App\Repositories\AgentWithdrawal;

use App\Models\AgentWithdrawal;
use App\Models\Role;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\AgentWithdrawal\AgentWithdrawalInterface;
use App\Services\Accounting\AgentSettlementService;
use Illuminate\Support\Facades\DB;

/**
 * Agent payouts: the step that was missing between "commission approved" and
 * the agent actually having their money.
 *
 * The status is the whole flow — requested, approved, paid, rejected — and the
 * wallet debit and the cash entry hang off it (AgentWithdrawalObserver), so
 * they cannot drift apart from what this screen says.
 */
class AgentWithdrawalRepository extends BaseRepository implements AgentWithdrawalInterface
{
    /** Relations eager-loaded on list / find. */
    protected array $with = ['agent', 'processedBy'];

    public function __construct(AgentWithdrawal $model, protected AgentSettlementService $settlement)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'agent_id'        => $request->agent_id,
            'reference'       => $request->reference ?: $this->settlement->nextWithdrawalReference(),
            'amount'          => $request->amount,
            'method'          => $request->method,
            'account_details' => $request->account_details,
            'status'          => $request->status ?: 'requested',
            'requested_on'    => $request->requested_on ?: now()->toDateString(),
            'note'            => $request->note,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => $q
                ->where('reference', 'like', "%{$search}%")
                ->orWhere('account_details', 'like', "%{$search}%"));
        }
        if (isset($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        $agents = User::where('role_id', Role::where('name', 'Agent')->value('id'))
            ->orderBy('name')->get(['id', 'name', 'email']);

        return [
            'agents'    => $agents,
            // What each agent could actually be paid right now, shown on the
            // form so nobody approves more than the wallet holds.
            'available' => $agents->mapWithKeys(fn (User $a) => [
                $a->id => $this->settlement->availableBalance($a->id),
            ]),
            'methods'   => AgentWithdrawal::METHODS,
            'statuses'  => AgentWithdrawal::STATUSES,
            'nextRef'   => $this->settlement->nextWithdrawalReference(),
        ];
    }

    /** A paid payout is money already sent; it is reversed, not deleted. */
    protected function guardDelete($model): ?string
    {
        if ($model->status === 'paid') {
            return 'This payout has already been paid. Set it back to approved first — that reverses the wallet and the cash entry.';
        }

        return null;
    }

    /* ---- Settlement actions --------------------------------------------- */

    public function approve($id)
    {
        return $this->transition($id, 'approved', ['requested'], ___('alert.successfully_updated'));
    }

    public function pay($id)
    {
        try {
            $withdrawal = $this->find($id);

            if ($withdrawal->status === 'paid') {
                return $this->responseWithError('This payout is already paid.');
            }

            // The wallet must still hold the money at the moment it is sent —
            // the balance can have moved since the request was raised.
            $balance = $this->settlement->walletBalance((int) $withdrawal->agent_id);
            if ((float) $withdrawal->amount > $balance + 0.009) {
                return $this->responseWithError(
                    'Wallet balance is only ' . currency_symbol() . number_format($balance, 2) . ' — cannot pay ' . currency_symbol()
                    . number_format((float) $withdrawal->amount, 2) . '.');
            }

            $this->settlement->payWithdrawal($withdrawal);
            $this->logActivity('paid', $withdrawal);

            return $this->responseWithSuccess('Payout sent and posted to the books.');
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function reject($id, ?string $note = null)
    {
        return $this->transition($id, 'rejected', ['requested', 'approved'],
            'Payout request rejected.', $note);
    }

    /**
     * The agent's own request. The amount is checked against what is actually
     * available — wallet balance less anything already requested — so the same
     * commission cannot be claimed twice.
     *
     * The check and the withdrawal row it guards are written under one lock:
     * two requests from the same agent arriving together must serialize, or
     * both could read the same available balance and together ask for more
     * than the wallet actually holds.
     */
    public function requestForAgent($request, int $agentId)
    {
        try {
            $amount = (float) $request->amount;

            return DB::transaction(function () use ($request, $agentId, $amount) {
                $this->settlement->lockAgent($agentId);

                $available = $this->settlement->availableBalance($agentId);

                if ($amount > $available + 0.009) {
                    return $this->responseWithError(
                        'You can withdraw up to ' . currency_symbol() . number_format($available, 2) . ' right now.');
                }

                $withdrawal = AgentWithdrawal::create([
                    'agent_id'        => $agentId,
                    'reference'       => $this->settlement->nextWithdrawalReference(),
                    'amount'          => $amount,
                    'method'          => $request->method,
                    'account_details' => $request->account_details,
                    'status'          => 'requested',
                    'requested_on'    => now()->toDateString(),
                    'note'            => $request->note,
                ]);

                $this->logActivity('requested', $withdrawal);

                return $this->responseWithSuccess('Withdrawal requested. The office will review it.');
            });
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /** Shared status change with a guard on which states may lead to it. */
    private function transition($id, string $status, array $from, string $message, ?string $note = null)
    {
        try {
            $withdrawal = $this->find($id);

            if (! in_array($withdrawal->status, $from, true)) {
                return $this->responseWithError(
                    "A {$withdrawal->status} payout cannot be marked {$status}.");
            }

            $withdrawal->forceFill(array_filter([
                'status'       => $status,
                'processed_on' => now()->toDateString(),
                'processed_by' => auth()->id(),
                'note'         => $note ?: $withdrawal->note,
            ]))->save();

            $this->logActivity($status, $withdrawal);

            return $this->responseWithSuccess($message);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }
}
