<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The package detail page showed the same hardcoded "What's included" list for
 * every package. These free-text columns (one item per line) make it per-package.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->text('inclusions')->nullable()->after('description');
            $table->text('exclusions')->nullable()->after('inclusions');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['inclusions', 'exclusions']);
        });
    }
};
