<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hotel and transport bookings gain a "Paid" lifecycle step (mirroring
 * bookings.payment_method): when the desk marks one paid, the receipt has to
 * say how the money arrived, so the ledger can put it in the right account.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('status');
        });

        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_bookings', fn (Blueprint $table) => $table->dropColumn('payment_method'));
        Schema::table('transport_bookings', fn (Blueprint $table) => $table->dropColumn('payment_method'));
    }
};
