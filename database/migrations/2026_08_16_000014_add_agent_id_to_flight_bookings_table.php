<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent attribution for flight requests.
 *
 * Phase 01 added agent_id to hotel_bookings and transport_bookings, the two
 * service tables the desk sold through then. Flights got the observer later
 * (Phase 04), and with it the agent path — so the attribution column lands
 * here now, matching the other two service tables exactly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flight_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('customer_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('flight_bookings', function (Blueprint $table) {
            $table->dropColumn('agent_id');
        });
    }
};
