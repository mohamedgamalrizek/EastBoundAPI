<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->string('invoice_no')->unique();
            $table->string('customer_name');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('unpaid');   // paid | unpaid | overdue
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
