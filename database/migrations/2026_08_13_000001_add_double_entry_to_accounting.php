<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Turns the single-entry transaction log into a real double-entry ledger.
     *
     * Before this, `accounts.balance` was a number typed by hand that no
     * transaction ever touched, and a transaction named one account only — so
     * the trial balance and the balance sheet could never balance, and nothing
     * the agency actually sold reached the books.
     *
     * After this:
     *   - every transaction carries a second leg (`contra_account_id`)
     *   - `accounts.balance` is derived: opening_balance + posted movement
     *   - `system_key` / `cash_type` name the accounts the posting rules need
     *     (receivable, sales, cash, bank …) instead of matching on the English
     *     account name
     *   - `source_type`/`source_id` tie an entry to the invoice or receipt that
     *     produced it, so it can be re-posted or reversed with its source
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('opening_balance', 14, 2)->default(0)->after('type');
            // Machine name for the accounts the posting rules must find.
            $table->string('system_key', 50)->nullable()->unique()->after('opening_balance');
            // none | cash | bank — drives the Cash Book and Bank Book.
            $table->string('cash_type', 10)->default('none')->after('system_key');
        });

        // Existing installs: whatever was in `balance` becomes the opening
        // balance, so the first rebuild reproduces today's figures.
        DB::table('accounts')->update(['opening_balance' => DB::raw('`balance`')]);

        Schema::table('account_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('contra_account_id')->nullable()->after('account_id')->index();
            $table->string('source_type', 100)->nullable()->after('description');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            // Maintained from the invoice's receipts; drives status + Due.
            $table->decimal('paid_amount', 12, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });

        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['contra_account_id', 'source_type', 'source_id']);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['opening_balance', 'system_key', 'cash_type']);
        });
    }
};
