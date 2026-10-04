<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transport products advertised on the public site (airport transfer, car
 * rental, coach…). `transport_bookings` records actual jobs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('icon')->nullable();               // Font Awesome class, e.g. fa-car-side
            $table->text('description')->nullable();
            $table->decimal('price_from', 12, 2)->default(0);
            $table->string('price_unit')->nullable();         // e.g. "/day"
            $table->string('booking_type')->default('car-rental'); // maps to /book/{type}
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('active');      // active | inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_services');
    }
};
