<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('dmc_external_reference', 120)->nullable();
            $table->string('dmc_payload_hash', 64)->nullable();
            $table->unique(['dmc_website_id', 'dmc_external_reference']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique(['dmc_website_id', 'dmc_external_reference']);
            $table->dropColumn(['dmc_external_reference', 'dmc_payload_hash']);
        });
    }
};
