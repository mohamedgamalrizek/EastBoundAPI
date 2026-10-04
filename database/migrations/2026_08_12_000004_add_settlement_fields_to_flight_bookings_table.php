<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A ticket's status told you it was cancelled or refunded but nothing else:
     * refunds are almost never the full fare — the airline keeps a penalty —
     * and the Refund Tracking page was showing the original fare as if it were
     * the amount returned.
     */
    public function up(): void
    {
        Schema::table('flight_bookings', function (Blueprint $table) {
            $table->decimal('refund_amount', 12, 2)->nullable()->after('fare');
            $table->decimal('penalty', 12, 2)->nullable()->after('refund_amount');
            $table->text('status_note')->nullable()->after('status');
            $table->timestamp('status_changed_at')->nullable()->after('status_note');
        });
    }

    public function down(): void
    {
        Schema::table('flight_bookings', function (Blueprint $table) {
            $table->dropColumn(['refund_amount', 'penalty', 'status_note', 'status_changed_at']);
        });
    }
};
