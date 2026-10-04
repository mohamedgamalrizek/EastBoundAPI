<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AgentCommission;
use App\Models\AgentWalletTransaction;
use App\Models\AgentWithdrawal;
use App\Models\Booking;
use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\TransportBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * What an agent earns, and how it actually reaches them.
 *
 * The chain, end to end:
 *
 *   booking marked paid   -> commission created at the agent's rate (pending)
 *                            books: Dr Agent Commission, Cr Agent Payable
 *   commission approved   -> credited to the agent's wallet (status paid)
 *   withdrawal requested  -> reserved against the wallet balance
 *   withdrawal paid       -> wallet debited, books: Dr Agent Payable, Cr Cash
 *
 * The expense is recognised when the commission is earned, not when it is
 * withdrawn — the agency owes it either way, which is what Agent Payable is.
 * The wallet is the agent's own view of that payable, so it is never written
 * by hand: every line here carries the commission or withdrawal that produced
 * it, and is rebuilt from those.
 */
class AgentSettlementService
{
    /** Withdrawal method -> the account the money leaves from. */
    public const METHOD_ACCOUNTS = [
        'Cash'  => 'cash',
        'Bank'  => 'bank',
        'bKash' => 'bank',
        'Nagad' => 'bank',
    ];

    /** Fallback commission rate when neither the agent nor Settings sets one. */
    public const DEFAULT_RATE = 7.0;

    public function __construct(protected LedgerService $ledger) {}

    /* =====================================================================
     | Commission
     * ================================================================== */

    /** The percentage this agent earns: their own rate, else the agency default. */
    public function rateFor(?User $agent): float
    {
        if ($agent && $agent->commission_rate !== null) {
            return (float) $agent->commission_rate;
        }

        return (float) (settings('agent_commission_rate') ?: self::DEFAULT_RATE);
    }

    /**
     * Keep any sale's commission in step with the sale itself.
     *
     * A commission exists once the sale is paid and belongs to an agent. If it
     * is cancelled, un-assigned or unpaid again, the commission is withdrawn —
     * unless it has already been approved into the wallet, in which case it
     * stays and has to be reversed deliberately. Works for tour bookings,
     * hotel stays and transport trips alike; each is keyed by its own morph.
     */
    public function syncCommission(Model $source): void
    {
        $sale = $this->commissionSale($source);

        if ($sale === null || empty($sale['agent_id']) || ! $sale['paid']) {
            $this->removeCommissionFor($source);

            return;
        }

        $existing = AgentCommission::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->where('is_auto', true)
            ->first();

        $agent  = User::find($sale['agent_id']);
        $rate   = $this->rateFor($agent);
        $amount = round((float) $sale['amount'] * $rate / 100, 2);

        if ($amount <= 0) {
            $this->removeCommissionFor($source);

            return;
        }

        // An approved commission is already in the agent's wallet; changing it
        // here would silently rewrite money they can see.
        if ($existing && $existing->status === 'paid') {
            return;
        }

        AgentCommission::updateOrCreate(
            [
                'source_type' => $source->getMorphClass(),
                'source_id'   => $source->getKey(),
                'is_auto'     => true,
            ],
            [
                'agent_id'      => $sale['agent_id'],
                'customer_id'   => $sale['customer_id'],
                // Legacy column: kept populated for tour bookings so the old
                // booking link and any queries keyed on it keep working.
                'booking_id'    => $source instanceof Booking ? $source->getKey() : null,
                'reference'     => $sale['reference'],
                'booking_ref'   => $sale['booking_ref'],
                'customer_name' => $sale['customer_name'],
                'amount'        => $amount,
                'rate'          => $rate,
                'status'        => $existing->status ?? 'pending',
                'earned_on'     => $sale['earned_on'],
            ]
        );
    }

    /**
     * Drop the pending auto commission a sale earned, if one exists.
     *
     * Used when a sale is deleted or stops qualifying (un-assigned, unpaid,
     * zero-priced). An approved commission is already in the agent's wallet
     * and is left alone — it has to be reversed deliberately.
     */
    public function removeCommissionFor(Model $source): void
    {
        AgentCommission::where('is_auto', true)
            ->where('status', 'pending')
            ->where(function ($q) use ($source) {
                $q->where('source_type', $source->getMorphClass())
                    ->where('source_id', $source->getKey());

                // Legacy rows backfilled before the source columns existed.
                if ($source instanceof Booking) {
                    $q->orWhere('booking_id', $source->getKey());
                }
            })
            ->get()
            ->each->delete();
    }

    /**
     * What a sale contributes to a commission: the paid amount, the agent who
     * sold it and the labels the commission row carries. Returns null for
     * sources the settlement engine does not recognise.
     */
    protected function commissionSale(Model $source): ?array
    {
        return match (true) {
            $source instanceof Booking => [
                'paid'          => $source->status === 'paid',
                'amount'        => (float) $source->amount,
                'agent_id'      => $source->agent_id,
                'customer_id'   => $source->customer_id,
                'customer_name' => (string) $source->customer_name,
                'reference'     => 'COM-B' . str_pad((string) $source->id, 5, '0', STR_PAD_LEFT),
                'booking_ref'   => 'BKG-' . $source->id,
                'earned_on'     => $source->updated_at?->toDateString() ?? now()->toDateString(),
            ],
            $source instanceof HotelBooking => [
                'paid'          => $source->status === 'Paid',
                'amount'        => (float) $source->amount,
                'agent_id'      => $source->agent_id,
                'customer_id'   => $source->customer_id,
                'customer_name' => $source->guest_name ?: ($source->customer?->name ?? 'Walk-in'),
                'reference'     => 'COM-H' . str_pad((string) $source->id, 5, '0', STR_PAD_LEFT),
                'booking_ref'   => $source->booking_no,
                'earned_on'     => $source->updated_at?->toDateString() ?? now()->toDateString(),
            ],
            $source instanceof TransportBooking => [
                'paid'          => $source->status === 'Paid',
                'amount'        => (float) $source->fare,
                'agent_id'      => $source->agent_id,
                'customer_id'   => $source->customer_id,
                'customer_name' => $source->customer_name ?: ($source->customer?->name ?? 'Walk-in'),
                'reference'     => 'COM-T' . str_pad((string) $source->id, 5, '0', STR_PAD_LEFT),
                'booking_ref'   => $source->booking_no,
                'earned_on'     => $source->updated_at?->toDateString() ?? now()->toDateString(),
            ],
            // A flight has no "paid" status — the sale is the ticketing: the
            // desk has issued a ticket number at a fare. A ticket later
            // cancelled or refunded stops qualifying, so its pending
            // commission is withdrawn on save.
            $source instanceof FlightBooking => [
                'paid'          => filled($source->ticket_no)
                                    && (float) $source->fare > 0
                                    && ! in_array($source->status, ['Cancelled', 'Refunded'], true),
                'amount'        => (float) $source->fare,
                'agent_id'      => $source->agent_id,
                'customer_id'   => $source->customer_id,
                'customer_name' => $source->passenger_name ?: ($source->customer?->name ?? 'Walk-in'),
                'reference'     => 'COM-F' . str_pad((string) $source->id, 5, '0', STR_PAD_LEFT),
                'booking_ref'   => $source->pnr ?: 'FLT-' . $source->id,
                'earned_on'     => $source->updated_at?->toDateString() ?? now()->toDateString(),
            ],
            default => null,
        };
    }

    /**
     * Commission earned: Dr Agent Commission (expense), Cr Agent Payable.
     * Posted as soon as the commission exists — the agency owes it from that
     * moment, whether or not the agent has been paid yet.
     */
    public function postCommission(AgentCommission $commission): void
    {
        $expense = Account::system('commission');
        $payable = Account::system('agent_payable');

        $this->ledger->postForSource($commission, [
            'account_id'        => $expense?->id,
            'contra_account_id' => $payable?->id,
            'txn_date'          => $commission->earned_on ?? now()->toDateString(),
            'account_name'      => $expense->name ?? 'Agent Commission',
            'type'              => 'expense',
            'amount'            => $commission->amount,
            'reference'         => $commission->reference,
            'description'       => 'Commission ' . $commission->reference . ' — '
                                    . ($commission->agent->name ?? 'agent')
                                    . ' on ' . ($commission->booking_ref ?: 'booking'),
        ]);
    }

    /**
     * Approve a commission: it becomes withdrawable money in the agent's
     * wallet. Idempotent — approving twice does not pay twice.
     */
    public function approveCommission(AgentCommission $commission): void
    {
        if ($commission->status !== 'paid') {
            $commission->forceFill([
                'status'      => 'paid',
                'approved_on' => now()->toDateString(),
            ])->save();
        }

        $this->walletEntry($commission, 'credit', $commission->amount,
            $commission->approved_on ?? now()->toDateString(),
            'Commission ' . $commission->reference . ' credited');
    }

    /** Take an approved commission back out of the wallet. */
    public function unapproveCommission(AgentCommission $commission): void
    {
        if ($commission->status === 'paid') {
            $commission->forceFill(['status' => 'pending', 'approved_on' => null])->save();
        }

        $this->removeWalletEntry($commission);
    }

    /* =====================================================================
     | Withdrawals
     * ================================================================== */

    /**
     * Lock the agent's user row for the rest of the current transaction — the
     * agent wallet has the same no-row-of-its-own shape the customer wallet
     * does, so this stands in for a wallet lock. Must be called from inside
     * DB::transaction so the lock actually lasts until the caller is done
     * reading the balance and writing against it.
     */
    public function lockAgent(int $agentId): void
    {
        User::whereKey($agentId)->lockForUpdate()->first();
    }

    /**
     * What the agent may ask for now: wallet balance less anything already
     * requested and not yet paid, so the same money cannot be claimed twice.
     */
    public function availableBalance(int $agentId): float
    {
        $reserved = (float) AgentWithdrawal::where('agent_id', $agentId)->open()->sum('amount');

        return round($this->walletBalance($agentId) - $reserved, 2);
    }

    /** Wallet balance = running balance on the agent's newest wallet line. */
    public function walletBalance(int $agentId): float
    {
        $latest = AgentWalletTransaction::where('agent_id', $agentId)
            ->orderBy('txn_date')->orderBy('id')
            ->get()
            ->last();

        return $latest ? (float) $latest->balance_after : 0.0;
    }

    /**
     * Mark a withdrawal paid. Saving it is all a caller has to do — the wallet
     * debit and the journal entry follow from the status (see
     * syncWithdrawalPosting), so no screen can move the money and forget the
     * books, or the other way round.
     */
    public function payWithdrawal(AgentWithdrawal $withdrawal): void
    {
        $withdrawal->forceFill([
            'status'       => 'paid',
            'processed_on' => $withdrawal->processed_on ?? now()->toDateString(),
            'processed_by' => auth()->id(),
        ])->save();
    }

    /**
     * Bring the wallet and the books in line with a withdrawal's status.
     *
     * Paid: the wallet is debited and cash leaves — Dr Agent Payable, Cr
     * Cash/Bank. Anything else (requested, approved, rejected, or a payment
     * undone): nothing has left the agency, so both are backed out again.
     */
    public function syncWithdrawalPosting(AgentWithdrawal $withdrawal): void
    {
        if ($withdrawal->status !== 'paid') {
            $this->removeWalletEntry($withdrawal);
            $this->ledger->unpostSource($withdrawal);

            return;
        }

        $date = $withdrawal->processed_on?->toDateString() ?? now()->toDateString();

        $this->walletEntry($withdrawal, 'debit', $withdrawal->amount, $date,
            'Withdrawal ' . $withdrawal->reference . ' (' . $withdrawal->method . ')');

        $payable = Account::system('agent_payable');
        $money   = Account::system(self::METHOD_ACCOUNTS[$withdrawal->method] ?? 'bank');

        $this->ledger->postForSource($withdrawal, [
            'account_id'        => $payable?->id,
            'contra_account_id' => $money?->id,
            'txn_date'          => $date,
            'account_name'      => $payable->name ?? 'Agent Payable',
            'type'              => 'payment',
            'amount'            => $withdrawal->amount,
            'reference'         => $withdrawal->reference,
            'description'       => 'Agent payout ' . $withdrawal->reference . ' — '
                                    . ($withdrawal->agent->name ?? 'agent'),
        ]);
    }

    /* =====================================================================
     | Agent invoices
     * ================================================================== */

    /**
     * Bring the wallet and the books in line with an agent invoice's status.
     *
     * Paid: the agency's charge to the agent is settled — Dr Wallet offset
     * (Agent Payable) or Dr Cash/Bank, Cr Sales Revenue. Settling from the
     * wallet also writes the debit line into the agent's statement, right next
     * to the commissions and payouts it nets against. Anything other than
     * "paid" backs both out again, so flipping the dropdown cannot leave a
     * phantom settlement behind.
     */
    public function syncAgentInvoicePosting(\App\Models\AgentInvoice $invoice): void
    {
        if ($invoice->status !== 'paid' || ! $invoice->agent_id || (float) $invoice->amount <= 0) {
            $this->removeWalletEntry($invoice);
            $this->ledger->unpostSource($invoice);

            return;
        }

        $date   = $invoice->paid_on?->toDateString() ?? now()->toDateString();
        $method = $invoice->method ?: 'Wallet';

        if ($method === 'Wallet') {
            $this->walletEntry($invoice, 'debit', $invoice->amount, $date,
                'Invoice ' . $invoice->invoice_no . ' settled from wallet');
            $money = Account::system('agent_payable');
        } else {
            $this->removeWalletEntry($invoice);
            $money = Account::system(self::METHOD_ACCOUNTS[$method] ?? 'cash');
        }

        $sales = Account::system('sales');

        // type "income" debits the contra account — money in, revenue out.
        $this->ledger->postForSource($invoice, [
            'account_id'        => $sales?->id,
            'contra_account_id' => $money?->id,
            'txn_date'          => $date,
            'account_name'      => $sales->name ?? 'Sales Revenue',
            'type'              => 'income',
            'amount'            => $invoice->amount,
            'reference'         => $invoice->invoice_no,
            'description'       => 'Agent invoice ' . $invoice->invoice_no . ' — '
                                    . ($invoice->agent->name ?? 'agent')
                                    . ' (' . $method . ')',
        ]);
    }

    /* =====================================================================
     | Wallet lines
     * ================================================================== */

    /**
     * Write (or rewrite) the wallet line belonging to a commission or a
     * withdrawal, then recompute the running balance for that agent.
     */
    public function walletEntry($source, string $type, $amount, $date, string $description): AgentWalletTransaction
    {
        $entry = AgentWalletTransaction::updateOrCreate(
            ['source_type' => $source->getMorphClass(), 'source_id' => $source->getKey()],
            [
                'agent_id'      => $source->agent_id,
                'reference'     => $source->reference,
                'type'          => $type,
                'amount'        => $amount,
                'description'   => $description,
                'txn_date'      => $date ?: now()->toDateString(),
                'balance_after' => 0, // set by the rebuild below
            ]
        );

        $this->rebuildWallet((int) $source->agent_id);

        return $entry->refresh();
    }

    /** Drop the wallet line a source produced, and re-run the balance. */
    public function removeWalletEntry($source): void
    {
        $deleted = AgentWalletTransaction::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->delete();

        if ($deleted) {
            $this->rebuildWallet((int) $source->agent_id);
        }
    }

    /**
     * Recompute `balance_after` down the agent's whole wallet statement.
     *
     * Running balances are recomputed rather than incremented so a back-dated
     * or deleted line cannot leave the statement telling a different story
     * from its own rows.
     */
    public function rebuildWallet(int $agentId): void
    {
        $balance = 0.0;

        AgentWalletTransaction::where('agent_id', $agentId)
            ->orderBy('txn_date')->orderBy('id')
            ->get()
            ->each(function (AgentWalletTransaction $entry) use (&$balance) {
                $balance += $entry->type === 'credit' ? (float) $entry->amount : -(float) $entry->amount;
                $entry->forceFill(['balance_after' => round($balance, 2)])->save();
            });
    }

    /** Rebuild every agent's wallet (used after seeding or a bulk import). */
    public function rebuildAllWallets(): void
    {
        AgentWalletTransaction::select('agent_id')->distinct()->pluck('agent_id')
            ->filter()
            ->each(fn ($agentId) => $this->rebuildWallet((int) $agentId));
    }

    /**
     * Summary for the agent portal and the back-office roster: what has been
     * earned, what is approved and withdrawable, and what is still owed.
     */
    public function summary(int $agentId): array
    {
        $commissions = AgentCommission::where('agent_id', $agentId);

        return [
            'earned'    => (float) (clone $commissions)->sum('amount'),
            'pending'   => (float) (clone $commissions)->where('status', 'pending')->sum('amount'),
            'approved'  => (float) (clone $commissions)->where('status', 'paid')->sum('amount'),
            'withdrawn' => (float) AgentWalletTransaction::where('agent_id', $agentId)
                                ->where('type', 'debit')->sum('amount'),
            'reserved'  => (float) AgentWithdrawal::where('agent_id', $agentId)->open()->sum('amount'),
            'balance'   => $this->walletBalance($agentId),
            'available' => $this->availableBalance($agentId),
        ];
    }

    /** Next withdrawal reference, e.g. WDL-1007. */
    public function nextWithdrawalReference(): string
    {
        $lastId = (int) AgentWithdrawal::max('id');

        return 'WDL-' . str_pad((string) ($lastId + 1001), 4, '0', STR_PAD_LEFT);
    }

    /** Date helper kept here so seeders and controllers agree on the format. */
    public function today(): string
    {
        return Carbon::now()->toDateString();
    }
}
