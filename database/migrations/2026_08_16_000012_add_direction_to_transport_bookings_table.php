<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Airport transfer and airport drop-off enquiries both raise a
 * TransportBooking with type "Airport" — the module's dispatch category is
 * the vehicle class, not which way the passenger is going, so nothing on the
 * row said whether staff needed to meet the passenger at arrivals or take
 * them to check-in. Every other type (Bus, Train, Launch, Car) leaves this
 * blank; it only ever holds Pickup / Drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->string('direction', 10)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->dropColumn('direction');
        });
    }
};
