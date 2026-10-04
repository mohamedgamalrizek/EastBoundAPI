<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->string('booking_no')->unique();
            $table->string('type');                       // Bus | Train | Launch | Car | Airport
            $table->string('customer_name');
            $table->string('route');
            $table->date('travel_date');
            $table->string('vehicle')->nullable();
            $table->decimal('fare', 10, 2);
            $table->string('status')->default('Booked');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_bookings');
    }
};
