<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agent attribution beyond tour bookings.
 *
 * Until now the only table that knew who sold something was `bookings`, and
 * agent_commissions could only point at it (booking_id). A hotel stay or a
 * transport trip sold by an agent had nowhere to record who sold it, and
 * nowhere to record what they earned.
 *
 * This makes agent_commissions polymorphic — source_type/source_id — so any
 * sale that carries an agent_id can earn that agent a commission, and adds
 * agent_id to the two service tables the desk sells through.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->string('source_type', 100)->nullable()->after('booking_id');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id']);
        });

        // Existing auto commissions were keyed by booking_id alone. Backfill
        // the source columns so re-saving a booking updates its commission
        // instead of silently creating a second one.
        $morph = (new Booking)->getMorphClass();
        DB::table('agent_commissions')
            ->whereNull('source_type')
            ->whereNotNull('booking_id')
            ->update(['source_type' => $morph, 'source_id' => DB::raw('booking_id')]);

        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('customer_id')->index();
        });

        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('customer_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['source_type', 'source_id']);
        });

        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->dropColumn('agent_id');
        });

        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->dropColumn('agent_id');
        });
    }
};
