<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->string('pnr')->unique();
            $table->string('passenger_name');
            $table->string('airline');
            $table->string('route');
            $table->date('flight_date');
            $table->string('ticket_no')->nullable();
            $table->decimal('fare', 10, 2);
            $table->string('status')->default('Confirmed'); // Confirmed | Pending | Cancelled | Refunded | Reissued
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_bookings');
    }
};
