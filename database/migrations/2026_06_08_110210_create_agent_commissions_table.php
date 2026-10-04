<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->string('reference')->unique();
            $table->string('booking_ref');
            $table->string('customer_name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('rate', 5, 2)->default(0);        // percentage
            $table->string('status')->default('pending');     // pending | paid
            $table->date('earned_on')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commissions');
    }
};
