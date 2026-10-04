<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The public header is a three-level structure (top link → mega column →
 * link) and the footer groups links under column headings. `menus` was flat,
 * so the whole navigation lived in the blade templates. These columns let the
 * CMS own it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')
                ->constrained('menus')->nullOnDelete();
            $table->string('icon')->nullable()->after('url');       // Font Awesome class
            $table->string('target')->nullable()->after('icon');    // e.g. _blank
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['icon', 'target']);
        });
    }
};
