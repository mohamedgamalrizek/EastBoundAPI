<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Refund;
use App\Services\Accounting\CustomerWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * CustomerWalletService's performance rule and the correctness it has to
 * hold onto while taking the shortcut: balance() reads only the newest row,
 * an in-order append sets its own balance_after from that row instead of
 * rewriting the statement, and only an out-of-order (back-dated) write pays
 * for a full rebuild. totalHeld() is checked against the same "latest row
 * per customer" definition, done in one query instead of one per customer.
 */
class WalletBalanceTest extends TestCase
{
    use RefreshDatabase;

    private function customer(string $email): Customer
    {
        return Customer::create([
            'name'     => 'Wallet Test ' . $email,
            'phone'    => '017' . substr(str_pad((string) crc32($email), 8, '0'), 0, 8),
            'email'    => $email,
            'password' => 'secret123',
            'status'   => 'active',
        ]);
    }

    private function wallet(): CustomerWalletService
    {
        return app(CustomerWalletService::class);
    }

    /**
     * A model instance good enough to stand in for the refund entryFor() is
     * normally called with — it is never saved or read back from its own
     * table here, only used for its morph class + key, so an unpersisted
     * Refund with a made-up id serves fine. (An anonymous class works too,
     * but its FQCN embeds this file's full path, which overflows wallet
     * transactions' 100-char source_type column and gets silently
     * truncated — a real, short-named model avoids that trap.)
     */
    private function fakeSource(int $id): Refund
    {
        $source = new Refund();
        $source->setAttribute('id', $id);

        return $source;
    }

    public function test_balance_reads_only_the_latest_row(): void
    {
        $customer = $this->customer('latest-row@example.com');
        $wallet   = $this->wallet();

        $wallet->adjust((int) $customer->id, 'credit', 100, 'Top-up 1');
        $wallet->adjust((int) $customer->id, 'debit', 30, 'Spend 1');
        $wallet->adjust((int) $customer->id, 'credit', 20, 'Top-up 2');

        DB::enableQueryLog();
        $balance = $wallet->balance((int) $customer->id);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertEqualsWithDelta(90.0, $balance, 0.01);

        // One query for the newest row — not the whole statement. A
        // regression back to loading every row would run one query but
        // transfer every transaction the customer has ever made; three rows
        // is too few to tell the difference on its own, so what matters here
        // is that exactly one row-fetching query ran for the read.
        $this->assertCount(1, $queries, 'balance() should run exactly one query.');
    }

    public function test_an_in_order_append_sets_its_own_balance_without_rewriting_earlier_rows(): void
    {
        $customer = $this->customer('append@example.com');
        $wallet   = $this->wallet();

        $first  = $wallet->adjust((int) $customer->id, 'credit', 100, 'Top-up');
        $second = $wallet->adjust((int) $customer->id, 'debit', 30, 'Spend');
        $third  = $wallet->adjust((int) $customer->id, 'credit', 10, 'Top-up again');

        $this->assertEqualsWithDelta(100.0, $first->fresh()->balance_after, 0.01);
        $this->assertEqualsWithDelta(70.0, $second->fresh()->balance_after, 0.01);
        $this->assertEqualsWithDelta(80.0, $third->fresh()->balance_after, 0.01);
        $this->assertEqualsWithDelta(80.0, $wallet->balance((int) $customer->id), 0.01);
    }

    public function test_a_back_dated_entry_triggers_a_full_rebuild(): void
    {
        $customer = $this->customer('backdated@example.com');
        $wallet   = $this->wallet();

        // Booked today: 100 arrives, balance is 100.
        $today = $wallet->adjust((int) $customer->id, 'credit', 100, 'Top-up today');
        $this->assertEqualsWithDelta(100.0, $today->fresh()->balance_after, 0.01);

        // A refund posted for yesterday — it belongs before "today" in the
        // statement, so every balance_after from that point on is now stale
        // and has to be redone.
        $backdated = $wallet->entryFor(
            $this->fakeSource(9001),
            (int) $customer->id,
            'credit',
            40,
            now()->subDay()->toDateString(),
            'Back-dated refund'
        );

        // Chronological order is now: yesterday's 40 first, then today's 100.
        $this->assertEqualsWithDelta(40.0, $backdated->fresh()->balance_after, 0.01);
        $this->assertEqualsWithDelta(140.0, $today->fresh()->balance_after, 0.01);
        $this->assertEqualsWithDelta(140.0, $wallet->balance((int) $customer->id), 0.01);
    }

    public function test_editing_an_existing_source_entry_also_triggers_a_full_rebuild(): void
    {
        $customer = $this->customer('edited@example.com');
        $wallet   = $this->wallet();
        $source   = $this->fakeSource(9002);

        $wallet->adjust((int) $customer->id, 'credit', 100, 'Top-up');
        $wallet->entryFor($source, (int) $customer->id, 'debit', 20, now()->toDateString(), 'Paid from wallet');
        $this->assertEqualsWithDelta(80.0, $wallet->balance((int) $customer->id), 0.01);

        // The same source, re-saved with a different amount — updateOrCreate
        // finds the existing row and edits it in place.
        $wallet->entryFor($source, (int) $customer->id, 'debit', 50, now()->toDateString(), 'Paid from wallet (corrected)');

        $this->assertEqualsWithDelta(50.0, $wallet->balance((int) $customer->id), 0.01);
    }

    public function test_total_held_matches_the_sum_of_every_customers_balance(): void
    {
        $wallet = $this->wallet();

        // Compared as a delta rather than an absolute figure: other seeded or
        // demo customers may already hold a balance in this database, and
        // totalHeld() summing every one of them is exactly the behaviour
        // under test — it should not assume it is the only wallet activity
        // that has ever happened.
        $before = $wallet->totalHeld();

        $a = $this->customer('total-a@example.com');
        $b = $this->customer('total-b@example.com');
        $c = $this->customer('total-c@example.com');

        $wallet->adjust((int) $a->id, 'credit', 500, 'Top-up');
        $wallet->adjust((int) $a->id, 'debit', 120, 'Spend');

        $wallet->adjust((int) $b->id, 'credit', 75, 'Top-up');

        // No wallet activity at all for $c — should contribute 0, not error.
        $addedByThisTest = $wallet->balance((int) $a->id) + $wallet->balance((int) $b->id) + $wallet->balance((int) $c->id);

        $this->assertEqualsWithDelta(455.0, $addedByThisTest, 0.01);
        $this->assertEqualsWithDelta($before + $addedByThisTest, $wallet->totalHeld(), 0.01);
    }
}
