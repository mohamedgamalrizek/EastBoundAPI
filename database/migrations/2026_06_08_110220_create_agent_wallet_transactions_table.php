<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->string('reference')->unique();
            $table->string('type')->default('credit');        // credit | debit
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('description');
            $table->date('txn_date')->nullable();
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_wallet_transactions');
    }
};
