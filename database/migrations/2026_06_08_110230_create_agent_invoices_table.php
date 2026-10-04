<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->string('invoice_no')->unique();
            $table->string('customer_name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('issued_on')->nullable();
            $table->date('due_on')->nullable();
            $table->string('status')->default('unpaid');       // paid | unpaid | overdue
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_invoices');
    }
};
