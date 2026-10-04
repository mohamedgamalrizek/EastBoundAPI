<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supplier payments left the agency's cash but the ledger never said which
     * account they left from — the statement knew the supplier was paid, the
     * books knew nothing at all. Recording the method lets a payment post as
     * Dr Accounts Payable, Cr Cash-or-Bank.
     */
    public function up(): void
    {
        Schema::table('supplier_transactions', function (Blueprint $table) {
            $table->string('method', 20)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_transactions', function (Blueprint $table) {
            $table->dropColumn('method');
        });
    }
};
