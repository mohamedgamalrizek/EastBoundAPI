<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'language')) {
                $table->string('language')->nullable()->after('website')->index();
            }

            if (! Schema::hasColumn('leads', 'country')) {
                $table->string('country')->nullable()->after('language')->index();
            }

            if (! Schema::hasColumn('leads', 'campaign_source')) {
                $table->string('campaign_source')->nullable()->after('country')->index();
            }
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_messages', 'language')) {
                $table->string('language')->nullable()->after('website')->index();
            }

            if (! Schema::hasColumn('contact_messages', 'country')) {
                $table->string('country')->nullable()->after('language')->index();
            }

            if (! Schema::hasColumn('contact_messages', 'campaign_source')) {
                $table->string('campaign_source')->nullable()->after('country')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            foreach (['campaign_source', 'country', 'language'] as $column) {
                if (Schema::hasColumn('contact_messages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            foreach (['campaign_source', 'country', 'language'] as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
