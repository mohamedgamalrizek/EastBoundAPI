<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Flight requests filed by the mobile app were written with a lowercase
 * "pending", which is in neither FlightRepository::STATUSES nor the
 * FlightBooking::TRANSITIONS keys. canMoveTo() therefore returned false for
 * every target status and the desk was told "A pending ticket cannot be
 * marked Confirmed" — the request could never be worked.
 *
 * The API now writes "Pending"; this brings the rows already filed into the
 * same vocabulary so they become actionable.
 */
return new class extends Migration
{
    public function up(): void
    {
        // The comparison is case-insensitive under the table's collation, so
        // this matches "pending" and rewrites it to the canonical spelling;
        // rows already spelled correctly are rewritten to the same value.
        foreach (\App\Repositories\Flight\FlightRepository::STATUSES as $status) {
            DB::table('flight_bookings')
                ->where('status', $status)
                ->update(['status' => $status]);
        }
    }

    public function down(): void
    {
        // Restoring the broken casing would only re-strand the same rows.
    }
};
