<?php

namespace App\Repositories\AgentCommission;

use App\Models\User;
use App\Models\Customer;
use App\Models\AgentCommission;
use App\Repositories\BaseRepository;
use App\Repositories\AgentCommission\AgentCommissionInterface;
use App\Models\Role;
use App\Services\Accounting\AgentSettlementService;

class AgentCommissionRepository extends BaseRepository implements AgentCommissionInterface
{
    /** Allowed option sets — mirror the agent_commissions migration. */
    public const STATUSES = ['pending', 'paid'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['agent', 'customer', 'booking'];

    public function __construct(AgentCommission $model, protected AgentSettlementService $settlement)
    {
        parent::__construct($model);
    }

    /* ---- Settlement actions --------------------------------------------- */

    /**
     * Release a commission to the agent: it is credited to their wallet and
     * becomes withdrawable. The expense was already booked when it was earned.
     */
    public function approve($id)
    {
        try {
            $commission = $this->find($id);

            if ($commission->isApproved()) {
                return $this->responseWithError('This commission is already credited.');
            }

            $this->settlement->approveCommission($commission);
            $this->logActivity('approved', $commission);

            return $this->responseWithSuccess('Commission credited to the agent wallet.');
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /** Take it back out of the wallet (raised in error, booking reversed). */
    public function unapprove($id)
    {
        try {
            $commission = $this->find($id);
            $this->settlement->unapproveCommission($commission);
            $this->logActivity('unapproved', $commission);

            return $this->responseWithSuccess('Commission moved back to pending.');
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'agent_id'      => $request->agent_id,
            'customer_id'   => $request->customer_id,
            'reference'     => $request->reference,
            'booking_ref'   => $request->booking_ref,
            'customer_name' => $request->customer_name,
            'amount'        => $request->amount,
            'rate'          => $request->rate,
            'status'        => $request->status,
            'earned_on'     => $request->earned_on,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('booking_ref', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            // Only actual agents — a commission for a back-office user is
            // meaningless, and the portal would never show it.
            'agents'    => User::where('role_id', Role::where('name', 'Agent')->value('id'))
                                ->orderBy('name')->get(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'statuses'  => self::STATUSES,
        ];
    }
}
