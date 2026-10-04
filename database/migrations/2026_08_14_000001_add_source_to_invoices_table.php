<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invoices were hard-linked to tour bookings (`booking_id`), so hotel stays,
 * transport trips, event-tour seats and hajj instalments could never be
 * invoiced at all — their revenue simply never reached the books. A nullable
 * polymorphic source lets any service document raise an invoice while the
 * existing booking_id path stays exactly as it was.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->nullableMorphs('source'); // source_type + source_id, indexed
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropMorphs('source');
        });
    }
};
