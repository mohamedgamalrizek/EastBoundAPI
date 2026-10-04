<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a published transport product to the booking type it is sold as, so
 * the public "from" price can be taken from real `transport_bookings.fare`
 * rather than a number typed into the CMS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_services', function (Blueprint $table) {
            // Matches transport_bookings.type: Bus | Train | Launch | Car | Airport
            $table->string('vehicle_type')->nullable()->after('booking_type');
        });
    }

    public function down(): void
    {
        Schema::table('transport_services', function (Blueprint $table) {
            $table->dropColumn('vehicle_type');
        });
    }
};
