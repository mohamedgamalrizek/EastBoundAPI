<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Published fare deals for the public flight-booking page. `flight_bookings`
 * stores issued tickets; this table is the marketing route/fare list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_routes', function (Blueprint $table) {
            $table->id();
            $table->string('origin');
            $table->string('origin_code')->nullable();
            $table->string('destination');
            $table->string('destination_code')->nullable();
            $table->string('airline')->nullable();
            $table->decimal('fare', 12, 2)->default(0);
            $table->string('trip_type')->default('One-way');  // One-way | Round-trip
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('active');      // active | inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_routes');
    }
};
