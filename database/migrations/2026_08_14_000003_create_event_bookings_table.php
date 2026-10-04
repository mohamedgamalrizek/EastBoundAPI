<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * event_tours is a product listing (title, seats, event date) — deliberately
 * so, see 2026_08_12_000006. What it never had is the client side: who bought
 * seats and for how much. This table is that record, shaped like the other
 * service bookings so the same billing observer can invoice it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_tour_id')->nullable()->constrained('event_tours')->nullOnDelete();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('booking_no')->unique();
            $table->string('customer_name'); // snapshot, like bookings/transport
            $table->unsignedInteger('seats')->default(1);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('Pending'); // Pending | Confirmed | Paid | Cancelled
            $table->string('payment_method', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_bookings');
    }
};
