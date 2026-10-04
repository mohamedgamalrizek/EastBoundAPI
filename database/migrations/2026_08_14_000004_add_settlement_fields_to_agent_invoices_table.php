<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * agent_invoices was a standalone CRUD: "paid" was a word in a dropdown that
 * moved no money. Settling one now needs to say when and how it was settled —
 * from the agent's commission wallet, or in cash/bank — so the observer can
 * post it and the wallet statement can show it next to commissions & payouts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_invoices', function (Blueprint $table) {
            $table->string('method', 20)->nullable()->after('status');   // Wallet | Cash | Bank | bKash | Nagad
            $table->date('paid_on')->nullable()->after('method');
        });
    }

    public function down(): void
    {
        Schema::table('agent_invoices', fn (Blueprint $table) => $table->dropColumn(['method', 'paid_on']));
    }
};
