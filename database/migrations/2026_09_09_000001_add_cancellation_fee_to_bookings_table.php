<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How much of a paid booking's amount the agency kept when a customer
 * cancelled it themselves (BookingController@cancel), per the
 * booking_cancellation_penalty_percent setting. Null for every booking that
 * was never cancelled, or cancelled with nothing paid to penalise.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('cancellation_fee', 12, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('cancellation_fee');
        });
    }
};
