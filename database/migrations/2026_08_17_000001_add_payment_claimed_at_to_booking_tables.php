<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks the moment a customer submits an offline "Pay Now" claim (bank
 * transfer, wallet, etc.), so the desk and the customer can both see a
 * claim is in flight without it being confused with `status`, which stays
 * the agency's call. Cleared once an admin changes the record's status.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['bookings', 'hotel_bookings', 'transport_bookings'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->timestamp('payment_claimed_at')->nullable()->after('payment_method');
            });
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'hotel_bookings', 'transport_bookings'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('payment_claimed_at');
            });
        }
    }
};
