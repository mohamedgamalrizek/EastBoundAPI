<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Customer;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The customer wallet, and its place in the accounts.
 *
 * A wallet balance is money the agency is holding for someone — a liability,
 * not income — so it belongs on the balance sheet. Until now it existed only
 * as a list of rows the app wrote, and a booking paid from the wallet was
 * posted as if cash had just come in.
 *
 * How each kind of line is booked:
 *
 *   top-up / manual credit  Dr Cash-or-Bank, Cr Customer Wallet
 *   manual debit            Dr Customer Wallet, Cr Cash
 *   paid a booking          nothing here — the receipt posts it
 *                           (Dr Customer Wallet, Cr Accounts Receivable)
 *   refunded to wallet      nothing here — the refund posts it
 *                           (Dr Refunds, Cr Customer Wallet)
 *
 * Lines carrying a source belong to that document; only standalone ones post
 * themselves, which is what keeps the same money from being counted twice.
 *
 * Every write below runs inside DB::transaction with the customer row locked
 * first (see lockCustomer). A wallet has no row of its own to lock, so the
 * customer row stands in for it: two requests touching the same wallet now
 * serialize rather than both reading the balance before either has written —
 * which is how a debit could pass its balance check twice against the same
 * money. Nesting is safe: a caller (PaymentIntentService, for instance) that
 * already opened a transaction just gets a savepoint here, not a second
 * connection-level transaction.
 */
class CustomerWalletService
{
    /** Top-up method -> the account the money arrived in. */
    public const METHOD_ACCOUNTS = [
        'Cash'  => 'cash',
        'Bank'  => 'bank',
        'Card'  => 'bank',
        'bKash' => 'bank',
        'Nagad' => 'bank',
    ];

    public function __construct(protected LedgerService $ledger) {}

    /**
     * Lock the customer row for the rest of the current transaction. Must be
     * called from inside DB::transaction — the lock lasts only as long as
     * that transaction does, and everything that reads or writes the wallet
     * afterwards has to happen before it commits or the guard is pointless.
     */
    public function lockCustomer(int $customerId): void
    {
        Customer::whereKey($customerId)->lockForUpdate()->first();
    }

    /**
     * Balance = running balance on the customer's newest wallet line. Reads
     * only that one row — the statement can be thousands of lines long, and
     * nothing here needs any of the others.
     */
    public function balance(int $customerId): float
    {
        $latest = $this->latestEntry($customerId);

        return $latest ? (float) $latest->balance_after : 0.0;
    }

    /** The customer's newest wallet line, in the same order rebuild() uses. */
    private function latestEntry(int $customerId): ?WalletTransaction
    {
        return WalletTransaction::where('customer_id', $customerId)
            ->orderByDesc('txn_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Add to (or take from) a wallet without a document behind it: a top-up,
     * a goodwill credit, a correction. These post to the journal themselves.
     */
    public function adjust(int $customerId, string $type, float $amount, string $description, ?string $method = 'Cash', ?string $reference = null): WalletTransaction
    {
        return DB::transaction(function () use ($customerId, $type, $amount, $description, $method, $reference) {
            $this->lockCustomer($customerId);

            $latest = $this->latestEntry($customerId);

            $entry = WalletTransaction::create([
                'customer_id' => $customerId,
                'reference'   => $reference ?: strtoupper($type === 'credit' ? 'TOPUP-' : 'WDR-') . strtoupper(substr(uniqid(), -6)),
                'type'        => $type,
                'amount'      => $amount,
                'description' => $description,
                'method'      => $method,
                'txn_date'    => now()->toDateString(),
                'balance_after' => 0, // set by applyOrRebuild below
            ]);

            $this->applyOrRebuild($entry, $latest, $customerId);

            return $entry->refresh();
        });
    }

    /**
     * Write the wallet line belonging to a receipt or a refund. Keyed by
     * source, so re-saving the document rewrites its line instead of adding
     * another one.
     */
    public function entryFor(Model $source, int $customerId, string $type, $amount, $date, string $description, ?string $reference = null): WalletTransaction
    {
        return DB::transaction(function () use ($source, $customerId, $type, $amount, $date, $description, $reference) {
            $this->lockCustomer($customerId);

            $sourceType = $source->getMorphClass();
            $sourceId   = $source->getKey();

            $existing = WalletTransaction::where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->first();

            $attributes = [
                'customer_id'   => $customerId,
                'source_type'   => $sourceType,
                'source_id'     => $sourceId,
                'reference'     => $reference ?: ($source->reference ?? class_basename($source) . '-' . $source->getKey()),
                'type'          => $type,
                'amount'        => $amount,
                'description'   => $description,
                'method'        => 'Wallet',
                'txn_date'      => $date ?: now()->toDateString(),
                'balance_after' => 0,
            ];

            if ($existing) {
                // Editing a line that already has its place in the statement
                // can change what every line after it should read (amount,
                // type or date all matter) — a full rebuild is the only safe
                // answer, same as a back-dated or deleted line.
                $existing->update($attributes);
                $this->rebuild($customerId);

                return $existing->refresh();
            }

            $latest = $this->latestEntry($customerId);

            $entry = WalletTransaction::create($attributes);

            $this->applyOrRebuild($entry, $latest, $customerId);

            return $entry->refresh();
        });
    }

    /**
     * A new line's running balance, without touching any other row — unless
     * it lands before the customer's current newest line (back-dated), in
     * which case every balance_after from that point on is now wrong and the
     * whole statement has to be redone.
     *
     * Must run under the same lock/transaction as the insert it follows.
     */
    private function applyOrRebuild(WalletTransaction $entry, ?WalletTransaction $latest, int $customerId): void
    {
        if ($latest && $entry->txn_date->lt($latest->txn_date)) {
            $this->rebuild($customerId);

            return;
        }

        $delta   = $entry->type === 'credit' ? (float) $entry->amount : -(float) $entry->amount;
        $balance = ($latest ? (float) $latest->balance_after : 0.0) + $delta;

        $entry->forceFill(['balance_after' => round($balance, 2)])->save();
    }

    /** Drop the wallet line a document produced. */
    public function removeEntryFor(Model $source): void
    {
        $entries = WalletTransaction::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->get();

        $customerIds = $entries->pluck('customer_id')->filter()->unique();

        DB::transaction(function () use ($entries, $customerIds) {
            $customerIds->each(fn ($customerId) => $this->lockCustomer((int) $customerId));

            $entries->each->delete();

            // A deleted line always needs the full rebuild — everything after
            // it in the statement has to shift down.
            $customerIds->each(fn ($customerId) => $this->rebuild((int) $customerId));
        });
    }

    /**
     * Post a standalone wallet line. Lines that belong to a receipt or a
     * refund are skipped: that document already puts them in the books, and
     * posting here as well would count the money twice.
     */
    public function postEntry(WalletTransaction $entry): void
    {
        if ($entry->source_type) {
            $this->ledger->unpostSource($entry);

            return;
        }

        $wallet = Account::system('customer_wallet');
        $money  = Account::system(self::METHOD_ACCOUNTS[$entry->method] ?? 'cash');

        // Top-up: cash comes in and the agency now owes it back.
        // Manual debit: the agency pays it out and owes that much less.
        $isCredit = $entry->type === 'credit';

        $this->ledger->postForSource($entry, [
            'account_id'        => $wallet?->id,
            'contra_account_id' => $money?->id,
            'txn_date'          => $entry->txn_date,
            'account_name'      => $wallet->name ?? 'Customer Wallet',
            // 'receipt' debits the contra (cash) and credits the wallet;
            // 'payment' does the reverse.
            'type'              => $isCredit ? 'receipt' : 'payment',
            'amount'            => $entry->amount,
            'reference'         => $entry->reference,
            'description'       => ($entry->description ?: 'Wallet movement')
                                    . ' — ' . ($entry->customer->name ?? 'customer'),
        ]);
    }

    /**
     * Recompute `balance_after` down a customer's whole statement, so a
     * back-dated, edited or deleted line cannot leave it disagreeing with its
     * rows. Only the exceptional paths call this now — a normal append
     * (adjust/entryFor for a new, in-order line) sets its own balance_after
     * from the previous latest row instead of rewriting everything.
     */
    public function rebuild(int $customerId): void
    {
        $balance = 0.0;

        WalletTransaction::where('customer_id', $customerId)
            ->orderBy('txn_date')->orderBy('id')
            ->get()
            ->each(function (WalletTransaction $entry) use (&$balance) {
                $balance += $entry->type === 'credit' ? (float) $entry->amount : -(float) $entry->amount;
                $entry->forceFill(['balance_after' => round($balance, 2)])->save();
            });
    }

    /** Rebuild every customer's wallet (after seeding or a bulk import). */
    public function rebuildAll(): void
    {
        WalletTransaction::select('customer_id')->distinct()->pluck('customer_id')
            ->filter()
            ->each(fn ($customerId) => $this->rebuild((int) $customerId));
    }

    /** Wallet summary for the portal and the back office. */
    public function summary(int $customerId): array
    {
        $mine = fn () => WalletTransaction::where('customer_id', $customerId);

        return [
            'balance' => $this->balance($customerId),
            'credited' => (float) $mine()->where('type', 'credit')->sum('amount'),
            'debited'  => (float) $mine()->where('type', 'debit')->sum('amount'),
        ];
    }

    /**
     * Total the agency is holding for all customers — matches account 2300.
     *
     * One query: rank each customer's lines newest-first and keep only the
     * top one, the same "latest wins" rule balance() applies per customer,
     * then sum those. Avoids running balance()'s query once per customer.
     */
    public function totalHeld(): float
    {
        $result = DB::selectOne('
            select coalesce(sum(balance_after), 0) as total
            from (
                select
                    balance_after,
                    row_number() over (
                        partition by customer_id
                        order by txn_date desc, id desc
                    ) as rn
                from wallet_transactions
            ) latest_per_customer
            where rn = 1
        ');

        return (float) ($result->total ?? 0);
    }
}
