<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who opened the ticket, when it was not a customer.
 *
 * A ticket could only be attributed to a `customer_id`, so an agent had no way
 * to raise one about their own business — the portal and the app could list
 * tickets but never file one. `assigned_to` is the staff member handling the
 * ticket, so it cannot carry this: reusing it would put the agent in the
 * queue as the handler of their own complaint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('support_tickets', 'raised_by')) {
                $table->unsignedBigInteger('raised_by')->nullable()->after('assigned_to')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'raised_by')) {
                $table->dropColumn('raised_by');
            }
        });
    }
};
